<?php
namespace App\Http\Middleware;
use DB;
use Closure;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LogAfterRequest {

    public function handle(Request $request, Closure $next)
    {
        DB::enableQueryLog();
        return $next($request);
    }

    public function terminate(Request $request, $response)
    {
        $formatter = new LineFormatter(null, null, true, true);
        $log = new Logger('access');
        $log->pushHandler((new StreamHandler(storage_path().'/logs/'.date('Y-m-d').'.log', Logger::INFO))->setFormatter($formatter) );
        $jsonHeader = json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $jsonRequest = print_r($request->all(), true);
        if(stripos($response->headers->get('Content-Type'), 'image')!==false) {
            $jsonResponse = 'content-type: '.$response->headers->get('Content-Type');
        } else if($response instanceof JsonResponse) {
            $jsonResponse = json_encode($response->getData(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        } else if($response instanceof Response) {
            $jsonResponse = $response->getContent();
        }
        $rpath = $request->path();
        if(strpos($rpath, 'admin')!==false) {
            $jsonResponse = '';
        }
        if(strpos($response->headers->get('Content-Type'), 'text/html')===0) {
            $jsonResponse = $response->headers->get('Content-Type');
        }
        if(preg_match('/\.js$|\.map$|\.png$|\.css$/', $rpath)) {
            return;
        }
        $sqlArr = [];
        $queries = DB::getQueryLog();
        foreach ($queries as $k=>$query) {
            $sql = $query['query'];       //查询语句sql
            $params = $query['bindings']; //查询参数
            $sql = str_replace('?', "'%s'", $sql);
            array_unshift($params, $sql);
            $sqlArr[] = call_user_func_array('sprintf', $params)."; {$query['time']}";
            unset($queries[$k]['bindings'], $queries[$k]['time']);
        }
        $jsonSql = print_r($sqlArr, true);
        $log->info('fullurl==>'.$request->fullUrl()."\nhttpmethod==>".$request->getMethod()."\nheader==>{$jsonHeader}\nrequest==>{$jsonRequest}\nresponse==>{$jsonResponse}\nsql==>{$jsonSql}");
    }

}