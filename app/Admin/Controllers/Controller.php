<?php

namespace App\Admin\Controllers;

use App\Model\Company;
use App\Model\AdminUser;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Encore\Admin\Facades\Admin;

class Content extends \Encore\Admin\Layout\Content {
    public $ctrl = null;
    public function render() {
        $items = [
            'header'      => $this->header,
            'description' => $this->description,
            'content'     => $this->build(),
        ];
        return view('admin::content', $items)->with(['ctrl'=>$this->ctrl])->render();
    }
}

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $user = '';
    protected $initialPreview = '/vendor/laravel-admin/AdminLTE/dist/img/no.png';
    public function user() {
        if(empty($this->user)) {
            $user = Admin::user()->getAttributes();
            $user = AdminUser::with('company')->find($user['id'])->toArray();
            $user['parent_ids'] = $user['company']['parent_ids'];
            $children = Company::where('parent_ids', 'regexp', "{$user['parent_ids']}(,|$)")->get();
            $parent   = Company::whereIn('id', explode(',', $user['parent_ids']))->get();
            $user['parent']   = $parent->pluck('name', 'id')->toArray();
            $user['children'] = $children->pluck('name', 'id')->toArray();
            $user['company_key'][$user['company_id']] = $user['parent'];
            $children->each(function ($item, $key) use(&$user) {
                $user['company_key'][$item->id] = $user['parent'];
                $child = explode(',', $item->parent_ids);
                foreach ($child as $id) {
                    $user['company_key'][$item->id][$id] = $user['parent'][$id] ?: $user['children'][$id];
                }
            });
            $this->user = $user;
        }
        return $this->user;
    }

    public function initialPreview() {
        return ['initialPreview'=>$this->initialPreview];
    }

    public function content($header=null, $description=null, \Closure $callback = null) {
        return new content(function ($content) use ($header, $description, $callback){
            $header and $content->header($header);
            $description and $content->description($description);
            $content->ctrl = $this;
            if ($callback instanceof \Closure) {
                $callback($content);
            }
        });
    }
}
