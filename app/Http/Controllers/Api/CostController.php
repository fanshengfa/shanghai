<?php
namespace App\Http\Controllers\Api;

use DB;
use App\Model\Driver;
use App\Fcore\Grid;
use App\Fcore\Facades\Fast;
use Illuminate\Http\Request;
use Hash;
use App\Util\UtHelp;
use App\Model\Order;
use App\Model\OrderOverdraft;
use App\Model\OrderCost;
use App\Model\Overdraft;
use Illuminate\Validation\Validator;

class CostController extends Controller {
    public function add(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order) use($request) {
            $conf= OrderCost::$conf;
            $lat = $request->get('lat');
            $lng = $request->get('lng');
            $fee_type = $request->get('fee_type');
            $pay_type = $request->get('pay_type');
            $card_id = $request->get('card_id');
            $fee_name = $request->get('fee_name');

            if($pay_type=='1' && empty($card_id)) {
                $validator->errors()->add('card_id_is_required', '没有预支卡，不能使用卡结算。');
                return;
            }

            $input = '';
            $feeStruct = [];
            foreach($conf as $val) {
                if($val['fee_type']!=$fee_type) {
                    continue;
                }
                $input = $val['fee_input_items'];
                $feeStruct = $val;
                break;
            }
            if(empty($input)) {
                $validator->errors()->add('fee_type_is_required', '错误的费用类型');
                return;
            }

            if($feeStruct['is_auto'] && empty($fee_name)) {
                $validator->errors()->add('fee_type_is_required', '自定义费用,费用名称不能空');
                return;
            } else {
                $fee_name = $feeStruct['fee_name'];
            }

            $fee = NULL;
            $remark = '';
            $other = [];
            foreach($input as $val) {
                $value = $request->get($val['key']);
                if(is_null($value) && $val['is_required']) {
                    $validator->errors()->add("{$val['key']}_is_required", "{$val['name']}不能为空");
                    return;
                }
                if($val['key']=='remark') {
                    $remark = $value;
                } else if($val['key']=='fee') {
                    $fee = $value;
                } else {
                    $other[$val['key']] = is_null($value) ? '' : $value;
                }
            }

            if(is_null($fee) || !is_numeric($fee) || $fee<0) {
                $validator->errors()->add("fee_is_required", "费用错误");
                return;
            }

            //上传费用照片
            $photo = '';
            $images = $request->file("photo");
            if(!empty($images)) {
                $filedir    = storage_path()."/upload/cost/";
                $ext        = $images->getClientOriginalExtension();
                $imagesName = "id_{$order->id}_".date('YmdHis')."_".mt_rand(1000,9999).".".$ext;
                $photo  = str_replace([storage_path(),DIRECTORY_SEPARATOR], ['','/'], $filedir.$imagesName);
                $images->move($filedir, $imagesName);
            }

            $orderCost = [
                'order_id'=>$order->id,
                'driver_id'=>$order->driver_id,
                'fee_name'=>$fee_name,
                'fee_type'=>$fee_type,
                'fee'=>$fee,
                'other'=>empty($other) ? '' : \json_encode($other, JSON_UNESCAPED_UNICODE),
                'lat'=>$lat,
                'lng'=>$lng,
                'photo'=>$photo,
                'remark'=>$remark,
                'pay_type'=>$pay_type,
                'created_at'=>date('Y-m-d H:i:s')
            ];
            OrderCost::create($orderCost);
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function list(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request);
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }

        $driver = $request->driver;
        $offset = $request->get('last_index', 0);
        $limit = $request->get('count', 10);
        $order_id = $request->get('order_id', 10);
        $data = Fast::grid(OrderCost::class, function(Grid $grid) use ($driver, $offset, $limit, $order_id){
            $where['driver_id'] = $driver->id;
            $where['order_id']  = $order_id;
            $grid->model()->where($where)
                ->offset($offset)
                ->limit($limit)
                ->orderBy('id', 'desc');
            $grid->disablePagination();
            $grid->column('id', 'id');
            $grid->column('order_id', 'order_id');
            $grid->column('driver_id', 'driver_id');
            $grid->column('fee', 'fee');
            $grid->column('fee_name', 'fee_name');
            $grid->column('fee_type', 'fee_type');
            $grid->column('other', 'other')->display(function() {
                $other = \json_decode($this->other, true);
                if(empty($other)) {
                    return [];
                } else {
                    array_walk($other, function(&$item, $key){
                        $item = ['key'=>$key, 'value'=>$item, 'name'=>array_get(OrderCost::$otherConf, "{$key}.name", '未知')];
                    });
                    return array_values($other);
                }
            });
            $grid->column('lat', 'lat');
            $grid->column('lng', 'lng');
            $grid->column('remark', 'remark');
            $grid->column('photo', 'photo');
            $grid->column('pay_type', 'pay_type');
            $grid->column('created_at', 'created_at');
        })->render('array');
        $count = min($limit, count($data));
        $list = [
            'count'      => $count,
            'last_index' => $offset + $count,
            'order_costs'     => $data
        ];
        return $this->resp($list);
    }

    public function total(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request);
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        $order_id = $request->get('order_id', 0);
        $fee_total = OrderCost::where(['order_id'=>$order_id])->sum('fee');
        $table = (new Overdraft)->getTable();
        $borrow_total = OrderOverdraft::join($table, "$table.id", '=', 'overdraft_id')->where(['order_id'=>$order_id,'is_advance'=>1])->sum('value');
        return $this->resp(['fee_total'=>$fee_total, 'borrow_total'=>$borrow_total]);
    }

    private function selfOrderCheck(Request $request, \Closure $after=null) {
        $driver = $request->driver;
        $all = $request->all();
        $rules = [
            'order_id' => 'required|numeric'
        ];
        $order = new \stdClass();
        list($code, $msg) = $this->checkReq($all, $rules, [], function($validator) use ($all, $driver, $after, &$order) {
            $order = Order::where(['id'=>$all['order_id'],'driver_id'=>$driver->id])->first();
            if(empty($order)) {
                $validator->errors()->add('order_id', '运单不存在');
                return;
            }
            call_user_func($after, $validator, $order);
        });
        return [$code, $msg];
    }
}
