<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\ModelForm;
use App\Model\Overdraft;
use Encore\Admin\Grid;
use Encore\Admin\Form;
class OverdraftController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('预支项管理');
            $content->body($this->grid());
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('预支项管理');
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return Admin::content(function (Content $content) use ($id){
            $content->header('预支项管理');
            $content->description(trans('admin.edit'));
            $content->body($this->form($id)->edit($id));
        });
    }

    public function grid() {
        return Admin::grid(Overdraft::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->where(['company_id'=>$user['company_id']]);
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '名称');
            $grid->column('unit', '单位');
            $grid->column('status', '状态')->display(function(){
                if($this->status=='-1') {
                    return '删除';
                } elseif($this->status=='0') {
                    return '正常';
                } else {
                    return '未知';
                }
            });
            $grid->column('description', '描述');
            $grid->filter(function ($filter) {
                $filter->useModal();
                $filter->like('name', '名称');
            });

            $grid->disableRowSelector();
            $grid->disableExport();
            //$grid->disableCreation();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form($id='')
    {
        return Admin::form(Overdraft::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->hidden('company_id')->default($user['company_id']);
            $form->text('name', '名称')->rules('required');
            $form->text('unit', '单位(如:米/升/块等) ');
            $form->radio('status', '状态')->options([
                '0'=>'正常', '-1'=>'删除'
            ]);
            $form->radio('is_advance', '是否是预支款项')->options([
                '0'=>'不是', '1'=>'是'
            ]);
            $form->textarea('description', '备注信息');
        });
    }
}
