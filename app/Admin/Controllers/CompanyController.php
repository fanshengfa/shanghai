<?php

namespace App\Admin\Controllers;

use Validator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Widgets\Box;
use App\Model\Company;

use App\Admin\Extensions\Tree\TreeTable;


class CompanyController extends Controller
{
    use ModelForm;

    public function index()
    {
        return $this->content('公司管理', null, function (Content $content) {
            $content->body($this->tree());
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->content('公司管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('公司管理', trans('admin.edit'), function (Content $content) use($id) {
            $content->body($this->form($id)->edit($id));
        });
    }

    public function tree() {
        $user = $this->user();
        $compayId = $user['company_id'];
        $company = Company::find($compayId);
        return new TreeTable($company, function (TreeTable $tree) use($user, $company) {
            $tree->query(function($query) use($user, $company) {
                return $query->where('parent_ids', 'like', "{$company->parent_ids}%");
            });
            $tree->header(function(){
                return ['公司名称'=>[],'创建时间'=>[]];
            });
            $tree->branch(function ($branch) {
                return ["{$branch['name']}(ID:{$branch['id']})", $branch['created_at']];
            });
            $tree->action(function ($branch, $edit, $delete) {
                return "{$edit}&nbsp;{$delete}";
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form($id='') {
        $user = $this->user();
        return Admin::form(Company::class, function (Form $form) use($id, $user) {
            if($id==$user['company_id'] && $user['company']['parent_id']) {
                $company = Company::find($user['company']['parent_id']);
            } else {
                $company = Company::find($user['company_id']);
            }
            $form->display('id', 'ID');
            $select = $form->select('parent_id', '上级')->options($company->where(function($query) use($id, $user) {
                if($id==$user['company_id'] && $user['company']['parent_id']) {
                    return $query->whereIn('id', array_keys($user['children']))->orWhere('id', $user['company']['parent_id']);
                } else {
                    return $query->whereIn('id', array_keys($user['children']));
                }
            })->selectOptions())->rules('nullable', []);
            if($id==$user['company_id']) {
                $select->readOnly();
            }
            $form->text('name', '名称')->rules('required');
            $form->datetime('created_at', '创建时间')->readOnly();
            $form->saving(function(Form $form) use($user) {
                $validator = Validator::make([],[]);
                $pCompany = Company::find($form->parent_id);
                if($form->model()->id && is_null($form->parent_id)) {
                    $form->parent_id = $form->model()->parent_id;
                    return;
                } elseif($pCompany->level>=2) {
                    $validator->errors()->add('parent_id', "只支持3级层级结构");
                    return back()->withInput()->withErrors($validator);
                } elseif($form->model()->id && $form->parent_id != $form->model()->parent_id && $form->model()->id==$user['company_id']) {
                    $validator->errors()->add('parent_id', "不能改变自己的所属关系");
                    return back()->withInput()->withErrors($validator);
                } elseif (!isset($user['children'][$form->parent_id])) {
                    $validator->errors()->add('parent_id', "层级关系错误");
                    return back()->withInput()->withErrors($validator);
                } elseif(true) {

                }
            });
            $form->saved(function (Form $form) {
                $company = Company::with(['parent', 'children'])->where(['id'=>$form->model()->id])->first();
                $company->parent_ids = trim($company->parent->parent_ids . ',' . $company->id, ',');
                $company->level      = $company->parent->level+1;
                $company->order      = $company->id;
                $company->save();
                $company->children->each(function ($item, $key) use($company) {
                    $item->parent_ids = trim($company->parent_ids . ',' . $item->id, ',');
                    $item->level      = $company->level+1;
                    $item->order      = $item->id;
                    $item->save();
                });
            });
        });
    }

}
