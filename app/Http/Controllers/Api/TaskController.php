<?php
namespace App\Http\Controllers\Api;

use App\Model\Auto;
use DB;
use App\Model\Driver;
use App\Fcore\Grid;
use App\Fcore\Facades\Fast;
use Illuminate\Http\Request;
use Hash;
use App\Util\UtHelp;
use App\Model\Order;
use App\Model\Card;
use App\Model\OrderPlace;
use App\Model\OrderLog;
use Illuminate\Validation\Validator;

class TaskController extends Controller {
    public function executing(Request $request) {
        return $this->resp(
            $this->getOrderList($request, Order::$executingStatus)
        );
    }

    public function history(Request $request) {
        return $this->resp(
            $this->getOrderList($request, Order::$historyStatus)
        );
    }

    public function detail(Request $request) {
        $driver= $request->driver;
        $all   = $request->all();
        $rules = [
            'order_id' => 'required|numeric'
        ];
        list($code, $msg) = $this->checkReq($all, $rules);
        if($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }

        $data = Fast::grid(Order::class, function(Grid $grid) use ($driver, $all){
            $where['driver_id'] = $driver->id;
            $where['id'] = $all['order_id'];
            $grid->model()->with(['overdraft'=>function($query){
                $query->withPivot(['id', 'value', 'status', 'return_value', 'description']);
            },'place'=>function($query){
                $query->with(['place.province', 'place.city', 'place.region'])->orderBy('order', 'asc')->orderBy('id', 'asc');
            }])->where($where);
            $grid->model()->collection(function($collection) {
                return $collection->map(function ($item, $key) {
                    $item->place_count = $item->place->count();
                    $item->target_city = $item->place->last()->address_c;
                    return $item;
                });
            });
            $grid->disablePagination();
            $grid->order('order')->display(function() {
                return [
                    'order_id'=>$this->id,
                    'status'=>$this->status,
                    'status_description'=>array_get(Order::$statusDict, $this->status, '未知'),
                    'description'=>$this->description,
                    'guess_start_time'=>$this->guess_start_time,
                    'guess_end_time'=>$this->guess_end_time,
                    'real_start_time'=>$this->real_start_time,
                    'real_end_time'=>$this->real_end_time,
                    'updated_at'=>$this->updated_at,
                    'created_at'=>$this->created_at,
                    'place_count'=>$this->place_count,
                    'target_city'=>$this->target_city
                ];
            });
            $grid->route('route')->display(function($route){
                return ['route_id'=>$route['id'], 'title'=>$route['title']];
            });
            $grid->driver('driver')->display(function($driver){
                return ['driver_id'=>$driver['id'], 'name'=>$driver['name'], 'mobile'=>$driver['mobile']];
            });
            $grid->assistant('assistant')->display(function($assistant){
                return null;
            });
            $grid->auto('auto')->display(function($auto){
                return ['auto_id'=>$auto['id'], 'auto_number'=>$auto['auto_number'], 'internal_number'=>$auto['internal_number']];
            });
            $grid->trailer('trailer')->display(function($trailer){
                return null;
            });
            $grid->dispatcher('dispatcher')->display(function($dispatcher){
                return ['dispatcher_id'=>$dispatcher['id'], 'name'=>$dispatcher['name'], 'mobile'=>$dispatcher['mobile']];
            });
            $grid->partner('partner')->display(function($partner){
                return [
                    'partner_id'=>$partner['id'],
                    'name'=>$partner['name'],
                    "contact_name"=>$partner['contact_name'],
                    "contact_tel"=>$partner['contact_tel']
                ];
            });
            $grid->column('place', 'places')->display(function($places){
                return array_map(function($place){
                    return [
                        'order_place_id'=>$place['id'],
                        'task_type'=>$place['task_type'],
                        'status'=>$place['status'],
                        'lat'=>$place['lat'],
                        'lng'=>$place['lng'],
                        'province'=>$place['place']['province']['short_name'],
                        'city'=>$place['place']['city']['short_name'],
                        'region'=>$place['place']['region']['name'],
                        'address'=>$place['place']['address'],
                        'arrived_time'=>$place['arrived_time'],
                        'leave_time'=>$place['leave_time']
                    ];
                }, $places);
            });
            $grid->column('overdraft', 'orderOverdrafts')->display(function($overdrafts){
                $overdrafts = (array)$this->overdraft;
                return array_map(function($overdraft){
                    return [
                        'order_overdraft_id' => $overdraft['pivot']['id'],
                        'order_id' => $overdraft['pivot']['order_id'],
                        'overdraft_id' => $overdraft['pivot']['overdraft_id'],
                        'value' => $overdraft['pivot']['value'],
                        'status' => '0',
                        'return_value' => $overdraft['pivot']['return_value'],
                        'description' => $overdraft['pivot']['description'],
                        'company_id' => $overdraft['company_id'],
                        'name' => $overdraft['name'],
                        'unit' => $overdraft['unit'],
                        'is_advance' => $overdraft['is_advance'],
                    ];
                }, $overdrafts);
            });
            $grid->card('orderCards')->display(function($cards){
                return array_map(function($card){
                    $card_desc  = Card::$typeDict[$card['card_type']].$card['card_number'].",金额";
                    $card_desc .= $card['pivot']['value'] - $card['pivot']['use_value'];
                    return [
                        'value'=>$card['pivot']['value'],
                        'use_value'=>$card['pivot']['use_value'],
                        'card_id'=>$card['pivot']['card_id'],
                        'card_number'=>$card['card_number'],
                        'begin_balance'=>$card['pivot']['value'],
                        'card_type'=>$card['card_type'],
                        'card_desc'=>$card_desc
                    ];
                    return $card;
                }, $cards);
            });

        })->render('object');
        return $this->resp($data);
    }

    public function start(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::LEAVE_DEST;
            $orderPlace->leave_time = time();
            $orderPlace->save();

            if($order->status == Order::DRIVER_ACCEPT) {
                $order->status = Order::TASK_START;
            }
            $order->place_id = $orderPlace->id;
            $order->real_start_time = time();
            $order->save();

            Driver::where(['id'=>$order->driver_id])->update(['current_auto_id'=>$order->auto_id]);
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function arrived(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::ARRIVED_DEST;
            $orderPlace->arrived_time = time();
            $orderPlace->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function loading(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::START;
            $orderPlace->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function unloading(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::START;
            $orderPlace->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function loadingSuccess(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::FINISH;
            $orderPlace->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function unloadingSuccess(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::FINISH;
            $orderPlace->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function placeSuccess(Request $request) {
        list($code, $msg) = $this->selfTaskCheck($request, function(Validator $validator, Order $order, OrderPlace $orderPlace){
            $orderPlace->status = OrderPlace::FINISH;
            $orderPlace->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function accept(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $order->last_status = $order->status;
            $order->status = 9;
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function reject(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $order->last_status = $order->status;
            $order->status = strval(-5);
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function goback(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $order->last_status = $order->status;
            $order->status = 3;
            $order->real_end_time = time();
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function gothome(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $order->last_status = $order->status;
            $order->status = -1;
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function empty(Request $request) {
        $driver = $request->driver;
        if(empty($driver->current_auto_id)) {
            return $this->resp();
        }
        Auto::find($driver->current_auto_id)->update(['run_status'=>1]);
        return $this->resp();
    }

    public function weight(Request $request) {
        $driver = $request->driver;
        if(empty($driver->current_auto_id)) {
            return $this->resp();
        }
        Auto::find($driver->current_auto_id)->update(['run_status'=>2]);
        return $this->resp();
    }

    public function writeoff(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $order->last_status = $order->status;
            $order->status = -2;
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function stop(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $order->last_status = $order->status;
            $order->status = -4;
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function pause(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order) use($request) {
            $type = $request->get('type');
            $lat = $request->get('lat');
            $lng = $request->get('lng');
            $description = $request->get('description');
            $typeArr = ['rest'=>'4', 'gas'=>'5', 'maintain'=>'6', 'traffic_jam'=>'7', 'accident'=>'8', 'auto_check'=>'10'];
            if (empty($typeArr[$type])) {
                $validator->errors()->add('pause_type_is_required', '未知任务暂停类型');
                return;
            }
            $status = $typeArr[$type];
            $order->last_status = $order->status;
            $order->status = $status;
            $order->save();

            //上传事件照片
            $photo = [];
            for($i=0;$i<10;$i++) {
                $images = $request->file("photo{$i}");
                if(empty($images)) {
                    continue;
                }
                $filedir    = storage_path()."/upload/event/";
                $ext        = $images->getClientOriginalExtension();
                $imagesName = "id_{$order->id}_".date('YmdHis')."_".mt_rand(1000,9999).".".$ext;
                $photo[$i]  = str_replace([storage_path(),DIRECTORY_SEPARATOR], ['','/'], $filedir.$imagesName);
                $images->move($filedir, $imagesName);
            }
            //$photo = empty($photo) ? NULL : \json_encode($photo, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            $orderLog = [
                'order_id'=>$order->id,
                'driver_id'=>$order->driver_id,
                'auto_id'=>$order->auto_id,
                'status'=>$status,
                'lat'=>$lat,
                'lng'=>$lng,
                'who_add'=>'driver',
                'photo'=>$photo,
                'remark' => $description,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            OrderLog::create($orderLog);
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    public function restart(Request $request) {
        list($code, $msg) = $this->selfOrderCheck($request, function(Validator $validator, Order $order){
            $last_status = array_reverse($order->last_status);
            $order->last_status = $order->status;
            foreach ($last_status as $status) {
                if(!in_array($status, Order::$pauseStatus)) {
                    $order->status = $status;
                    break;
                }
            }
            $order->save();
        });
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        return $this->resp();
    }

    private function selfTaskCheck(Request $request, \Closure $after=null) {
        $driver = $request->driver;
        $all = $request->all();
        $rules = [
            'order_id' => 'required|numeric',
            'place_id' => 'required|numeric',
        ];
        $order = new \stdClass();
        $orderPlace = new \stdClass();
        list($code, $msg) = $this->checkReq($all, $rules, [], function($validator) use ($all, $driver, $after, &$order, &$orderPlace) {
            $order = Order::where(['id'=>$all['order_id'],'driver_id'=>$driver->id])->first();
            if(empty($order)) {
                $validator->errors()->add('order_id', '运单不存在');
                return;
            }
            $orderPlace = OrderPlace::where(['id'=>$all['place_id'],'order_id'=>$all['order_id']])->first();
            if(empty($orderPlace)) {
                $validator->errors()->add('place_id', '未知装卸地址');
                return;
            }
            call_user_func($after, $validator, $order, $orderPlace);
        });
        return [$code, $msg];
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

    private function getOrderList(Request $request, $status=array()) {
        $driver = $request->driver;
        $offset = $request->get('last_index', 0);
        $limit = $request->get('count', 10);
        $data = Fast::grid(Order::class, function(Grid $grid) use ($driver, $offset, $limit, $status){
            $where['driver_id'] = $driver->id;
            /*
            RoutePlace::with(['place'=>function($query){
                $query->with(['province', 'city', 'region']);
            }])->where(['route_id'=>$route_id])->get()->toArray();
            */
            $grid->model()->with(['place'=>function($query){
                $query->with(['place.province', 'place.city', 'place.region'])->select(['id','order_id', 'route_id', 'place_id'])->orderBy('id', 'desc');
            }])->where($where)->whereIn('status', $status)
                ->offset($offset)
                ->limit($limit)
                ->orderBy('id', 'desc');
            $grid->model()->collection(function($collection) {
                return $collection->map(function ($item, $key) {
                    $item->place_count = $item->place->count();
                    $place = $item->place->last();
                    $place = $place ? $place->toArray() : [];
                    $placeTarget = array_filter([
                        array_get($place, 'place.province.name', ''),
                        array_get($place, 'place.city.name', ''),
                        array_get($place, 'place.region.name', '')
                    ]);
                    $item->target_city = join('-', $placeTarget);
                    return $item;
                });
            });
            $grid->disablePagination();
            $grid->order('order')->display(function() {
                return [
                    'order_id'=>$this->id,
                    'status'=>$this->status,
                    'status_description'=>array_get(Order::$statusDict, $this->status, '未知'),
                    'description'=>$this->description,
                    'guess_start_time'=>$this->guess_start_time,
                    'guess_end_time'=>$this->guess_end_time,
                    'real_start_time'=>$this->real_start_time,
                    'real_end_time'=>$this->real_end_time,
                    'updated_at'=>$this->updated_at,
                    'created_at'=>$this->created_at,
                    'place_count'=>$this->place_count,
                    'target_city'=>$this->target_city
                ];
            });
            $grid->route('route')->display(function($route){
                return ['route_id'=>$route['id'], 'title'=>$route['title'], 'description'=>$route['description']];
            });
            $grid->driver('driver')->display(function($driver){
                return ['driver_id'=>$driver['id'], 'name'=>$driver['name'], 'mobile'=>$driver['mobile']];
            });
            $grid->assistant('assistant')->display(function($assistant){
                return null;
            });
            $grid->auto('auto')->display(function($auto){
                return ['auto_id'=>$auto['id'], 'auto_number'=>$auto['auto_number'], 'internal_number'=>$auto['internal_number']];
            });
            $grid->trailer('trailer')->display(function($trailer){
                return null;
            });
            $grid->partner('partner')->display(function($partner){
                return ['partner_id'=>$partner['id'], 'name'=>$partner['name']];
            });
        })->render('array');
        $count = min($limit, count($data));
        $list = [
            'count'      => $count,
            'last_index' => $offset + $count,
            'orders'     => $data
        ];
        return $list;
    }
}
