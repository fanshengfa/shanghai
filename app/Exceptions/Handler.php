<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            if(strpos($request->path(), 'api/')!==false) {
                $path = str_replace('api/', '', $request->path());
                $errorResp = [
                    "state"  => 0,
                    "method" => "{$path}",
                    "data"   => [
                        "code" => strval('notfound'),
                        "msg"  => strval('请求的地址不存在')
                    ]
                ];
                return  response()->json($errorResp, 200, [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }
        } elseif($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            if(strpos($request->path(), 'api/')!==false) {
                $path = str_replace('api/', '', $request->path());
                $errorResp = [
                    "state"  => 0,
                    "method" => "{$path}",
                    "data"   => [
                        "code" => strval('methoderror'),
                        "msg"  => strval('提交方式错误')
                    ]
                ];
                return  response()->json($errorResp, 200, [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }
        }
        return parent::render($request, $exception);
    }
}
