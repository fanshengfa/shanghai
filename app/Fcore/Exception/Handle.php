<?php

namespace App\Fcore\Exception;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class Handle
{
    /**
     * Render exception.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    public static function renderException(\Exception $exception)
    {
        $error = new MessageBag([
            'type'      => get_class($exception),
            'message'   => $exception->getMessage(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
        ]);

        $message = $exception->getMessage();
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $message = "没有找到数据[".$exception->getModel()."]";
        }
        return response()->json([
            'status'  => 'error',
            'error' => [
                'status_code' => strval("601"),
                'message' => $message
            ]
        ]);
        /*
        $errors = new ViewErrorBag();
        $errors->put('exception', $error);
        return view('admin::partials.exception', compact('errors'))->render();
        */
    }
}
