<?php

namespace App\Admin\Controllers;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Model\Partner;
use App\Model\Order;
class PartnerController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('客户管理');
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
            $content->header('客户管理');
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return Admin::content(function (Content $content) use ($id){
            $content->header('客户管理');
            $content->description(trans('admin.edit'));
            $content->body($this->form($id)->edit($id));
        });
    }

    public function show($id) {
        return Admin::content(function (Content $content) use ($id){
            $content->header('客户管理');
            $content->description(trans('admin.view'));
            $content->row(function(Row $row) use ($id) {
                $partner = (new Partner())->where('id', $id)->getModel();
                $baseInfoBox = new Box('基本信息', new Grid($partner, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('name', '客户名称');
                    $grid->column('number', '客户编号');
                    $grid->column('address_p', '所在地')->display(function(){
                        return "{$this->address_p} {$this->address_c} {$this->address_r}";
                    })->label();
                    $grid->column('address', '详细地址')->display(function(){
                        return "{$this->address} 坐标（{$this->lng},{$this->lat}）";
                    })->label();
                    $grid->column('domain_name', '域名');
                    $grid->column('logo_url', 'LOGO')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $contactInfoBox = new Box('联系方式', new Grid($partner, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('contact_name', '联系人');
                    $grid->column('contact_tel', '联系电话');
                    $grid->column('contact_email', '联系邮箱');
                    $grid->column('description', '备注信息');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $row->column(4, function(Column $column) use($baseInfoBox, $contactInfoBox) {
                    $column->append($baseInfoBox->solid()->style('primary'));
                    $column->append($contactInfoBox->solid()->style('info'));
                });

                $orderBox = new Box('历史运单记录', Admin::grid(Order::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('partner_id', $id)->paginate(20, ['*'], 'orderpage');
                    $grid->column('number', '运单编号');
                    $grid->column('route.title', '项目');
                    $grid->column('status', '状态')->display(function(){
                        return isset(Order::$statusDict[$this->status]) ? Order::$statusDict[$this->status] : '未知';
                    })->label();
                    $grid->column('created_at', '时间')->display(function(){
                        return "创建：{$this->created_at}<br/>更新：{$this->updated_at}";
                    });
                    $grid->actions(function (Grid\Displayers\Actions $actions) {
                        $actions->disableDelete();
                        $actions->disableEdit();
                        $actions->append('<a href="'.admin_url('order/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
                    });
                    $grid->disableRowSelector();
                    $grid->setView('admin.grid.simple');
                }));
                $row->column(8, function(Column $column) use($orderBox) {
                    $column->append($orderBox->solid()->style('primary'));
                });
            });
        });
    }

    public function grid() {
        return Admin::grid(Partner::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->where(['company_id'=>$user['company_id']]);
            $grid->column('id', 'id')->sortable();
            $grid->column('name', '客户名称');
            $grid->column('number', '客户编号');
            $grid->column('contact_name', '联系人');
            $grid->column('contact_tel', '联系电话');
            $grid->filter(function ($filter) {
                $filter->useModal();
                $filter->like('name', '客户名称');
                $filter->like('number', '客户编号');
            });
            $grid->actions(function(Grid\Displayers\Actions $actions){
                $actions->append('<a href="'.admin_url('partner/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
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
        return Admin::form(Partner::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->tab('基本信息', function (Form $form) use($id, $user) {
                $form->display('id', 'ID');
                $form->hidden('company_id')->default($user['company_id']);
                $form->text('name', '客户名称')->rules('required');
                $form->text('number', '客户编号(可选)');
                $form->url('domain_name', '域名(可选)');
                $form->image('logo_url', 'LOGO')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->addElementClass("rand_".mt_rand(1000,9999));
            })->tab('联系方式', function (Form $form) use($id) {
                $form->text('contact_name', '联系人')->rules('required');
                $form->mobile('contact_tel', '联系电话');
                $form->email('contact_email', '联系邮箱(可选)');
                $form->text('description', '备注信息');
            });
        });
    }
}
