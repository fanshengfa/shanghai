<?php

namespace App\Http\Controllers\Api;

use Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function checkReq(Array $all=array(), Array $rules=array(), Array $msg=array(), \Closure $after=null) {
        $validator = Validator::make($all, $rules, $msg);
        if($after) {
            $validator->after($after);
        }
        if ($validator->fails()) {
            $error = $validator->errors()->messages();
            $key = key($error);
            return [$key, $error[$key][0]];
        }
        return [false, false];
    }

    public function resp($data=NULL, $code=NULL, $msg=NULL) {
        //{"state":1,"method":"driver/signin","data":{"driver":{"driver_id":305,"token":"cebb353b296e9d87e111fa7d39d3d7d9","mobile":"15100000000","name":"田野2","internal_num":"XL0002"},"company":{"company_id":3,"name":"北京最好的公司"},"driver_group":{"driver_group_id":18,"name":"2222"}}}
        //{"state":0,"method":"driver/signin","data":{"code":"mobile_or_pwd_error","msg":"手机号或密码错误"}}
        $path = str_replace('api/', '', app('request')->path());
        if($code || $msg) {
            $resp = [
                "state"  => 0,
                "method" => "{$path}",
                "data"   => [
                    "code" => strval($code),
                    "msg"  => strval($msg)
                ]
            ];
        } else {
            $resp = [
                "state"  => 1,
                "method" => "{$path}",
                "data"   => is_null($data) ? new \stdClass() : $data,
            ];
        }
        return response()->json($resp, 200, [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
}
