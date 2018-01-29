<?php

namespace App\Admin\Middleware;

use Encore\Admin\Facades\Admin;
use App\Model\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Encore\Admin\Middleware\Pjax;

class VerifyCompany {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        /*
        $path = $request->path();
        $match = '';
        if(!preg_match('/^(.*)\/(\d+)|(\/edit)?$/', $path, $match)) {
            return $next($request);
        }
        $model = "\\App\Model\\".ucfirst($match[1]);
        $id = $match[2];
        */
        $action = $request->route()->getAction();
        $model = str_replace([$action['namespace'].'\\', 'Controller@'.$request->route()->getActionMethod()], '', $request->route()->getActionName());
        $id    = $request->route()->parameter(lcfirst(str_replace('.'.$request->route()->getActionMethod(),'',$action['as'])), '');
        $model = "\\App\Model\\".ucfirst($model);
        if(!class_exists($model) || empty($id)) {
            return $next($request);
        }
        $model = (new $model);
        if(!$model->isFillable('company_id')) {
            return $next($request);
        }
        $model = $model->where($model->getKeyName(), $id)->first();
        if(empty($model)) {
            return Pjax::respond(response(Admin::content()->withError(trans('admin.deny'))));
        }
        $user  = Admin::user()->getAttributes();
        $company = Company::where(function($query) use($user, $model) {
            $query->whereRaw("FIND_IN_SET('{$user['company_id']}', parent_ids)")
                  ->whereRaw("FIND_IN_SET('{$model->company_id}', parent_ids)")
                  ->where('id', $model->company_id);
        })->first();
        if($company) {
            return $next($request);
        }
        return Pjax::respond(response(Admin::content()->withError(trans('admin.deny'))));
    }

}
