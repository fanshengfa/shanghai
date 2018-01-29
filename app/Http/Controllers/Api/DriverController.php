<?php
namespace App\Http\Controllers\Api;

use DB;
use App\Model\Driver;
use App\Fcore\Grid;
use App\Fcore\Facades\Fast;
use Validator;
use Illuminate\Http\Request;
use Hash;
use App\Util\UtHelp;
use App\Model\Order;
use App\Model\Auto;
use App\Model\AutoKilometer;

class DriverController extends Controller {
    public function signin(Request $request) {
        $all   = $request->all();
        $rules = [
            'mobile' => 'required',
            'pwd' => 'required',
        ];
        $msg   = [
            'mobile.required' => '手机号是必填的',
            'pwd.required' => '密码是必填的',
        ];
        list($code, $msg) = $this->checkReq($all, $rules, $msg);
        if($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        $driver = Driver::where('mobile', $all['mobile'])->first();
        if(!Hash::check($all['pwd'], $driver->pwd)){
            return $this->resp(NULL,'pwd_error', '密码不对');
        }
        if(empty($driver->token) || $driver->mobile!='15100000000') {
            $driver->update(['token'=>md5(time().mt_rand(1000, 9999).uniqid())]);
        }
        $data = Fast::grid(Driver::class, function(Grid $grid) use ($driver){
            $where['id'] = $driver->id;
            $grid->model()->where($where)->orderBy('id', 'desc');
            $grid->driver('driver')->display(function(){
                return ['driver_id'=>$this->id, 'token'=>$this->token, 'mobile'=>$this->mobile, 'name'=>$this->name, 'internal_num'=>$this->internal_num];
            });
            $grid->company('company')->display(function($company){
                return ['company_id'=>$company['id'], 'name'=>$company['name']];
            });
            $grid->fleet('driver_group')->display(function($fleet){
                return ['driver_group_id'=>$fleet['id'], 'name'=>$fleet['name']];
            });
        })->render('object');
        return $this->resp($data);
    }

    public function signout(Request $request) {
        $driverId = $request->header('Driver-Id');
        Driver::where(['id'=>$driverId])->update(['token'=>'']);
        return $this->resp();
    }

    public function resetpwd(Request $request) {
        $driver= $request->driver;
        $all   = $request->all();
        $rules = [
            'opwd' => 'required|min:6|max:16',
            'npwd' => 'required|min:6|max:16',
        ];
        list($code, $msg) = $this->checkReq($all, $rules, [], function($validator) use ($all, $driver) {
            if (!Hash::check($all['opwd'], $driver->pwd)) {
                $validator->errors()->add('opwd', '原密码错误');
            }
        });
        if($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        $driver->update(['pwd'=>bcrypt($all['npwd'])]);
        return $this->resp();
    }

    public function locationReport(Request $request) {
        $driver= $request->driver;
        $all   = $request->all();
        $rules = [
            'lat' => 'required|lat',
            'lng' => 'required|lng',
        ];
        list($code, $msg) = $this->checkReq($all, $rules);
        if($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        if(empty($driver->current_auto_id)) {
            return $this->resp();
        }
        $auto = Auto::with('kilometer')->find($driver->current_auto_id);
        //更新空驶重载里程,更新频率2分钟
        if(!empty($auto->lat) && !empty($auto->lng)) {
            if(empty($auto->kilometer)) {
                AutoKilometer::create(['auto_id'=>$auto->id]);
            } elseif(time() - strtotime($auto->kilometer->updated_at) > 120){
                $distance = UtHelp::distanceCalculate($auto->lng, $auto->lat, $all['lng'], $all['lat']);
                if($auto->run_status==0) {
                    $auto->kilometer->update(['unknown'=>DB::raw("unknown+{$distance}")]);
                } elseif($auto->run_status==1) {
                    $auto->kilometer->update(['empty'=>DB::raw("empty+{$distance}")]);
                } elseif($auto->run_status==2) {
                    $auto->kilometer->update(['weight'=>DB::raw("weight+{$distance}")]);
                }
                if($auto->status==1) {
                    $order = Order::where(['auto_id'=>$auto->id, 'company_id'=>$auto->company_id, 'status'=>Order::$executingStatus])->orderby('id', 'desc')->first();
                    if($order && $auto->run_status==1) {
                        $order->update(['kilometer'=>DB::raw("kilometer+".sprintf("%0.2f", $distance/1000))]);
                    } elseif($order && $auto->run_status==2) {
                        $order->update(['cargo_kilometer'=>DB::raw("cargo_kilometer+".sprintf("%0.2f", $distance/1000))]);
                    }
                }
            }
        }
        //更新经纬度
        $auto->update(['lat'=>$all['lat'],'lng'=>$all['lng'],'loc_report_time'=>time()]);
        return $this->resp();
    }

    public function getuiReport(Request $request)
    {
        $driver = $request->driver;
        $all = $request->all();
        $rules = [
            'clientid' => 'required'
        ];
        list($code, $msg) = $this->checkReq($all, $rules);
        if ($code || $msg) {
            return $this->resp(NULL, $code, $msg);
        }
        $driver->clientid = $all['clientid'];
        $driver->save();
        return $this->resp();
    }
}
