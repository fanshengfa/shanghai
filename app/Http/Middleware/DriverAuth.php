<?php

namespace App\Http\Middleware;

use App\Model\Driver;
use App\Http\Controllers\Api\ResultController as Result;
use Closure;
use Illuminate\Http\Request;

class DriverAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $driverId = $request->header('Driver-Id');
        $token    = $request->header('Token');
        $path = str_replace('api/', '', $request->path());

        $errorResp = [
            "state"  => 0,
            "method" => "{$path}",
            "data"   => [
                "code" => strval('driver_token_error'),
                "msg"  => strval('错误的token')
            ]
        ];
        if (empty($driverId) || empty($token)) {
            return  response()->json($errorResp, 200, [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }
        $driver = Driver::where([['id','=', $driverId],['token', '=', $token]])->first();
        if (empty($driver)) {
            return  response()->json($errorResp);
        } else {
            $request->driver = $driver;
            return $next($request);
        }
    }
}
