<?php

namespace App\Admin\Controllers;

use App\Model\Driver;
use App\Model\Route;
use App\Model\RouteGoods;
use App\Model\RoutePlace;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid\Filter;
use App\Model\Card;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Model\Auto;
use App\Model\Overdraft;
use App\Model\Fleet;


class FleetController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('车队管理');
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
            $content->header('车队管理');
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return Admin::content(function (Content $content) use ($id){
            $content->header('车队管理');
            $content->description(trans('admin.edit'));
            $content->body($this->form($id)->edit($id));
        });
    }

    public function search(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return Fleet::where(function($query) use ($q) {
            $query->where('auto_number', 'like', "%$q%");
        })->orWhere(function($query) use ($q) {
            $query->where('internal_num', 'like', "%$q%");
        })->select(['id', \DB::raw('auto_number as text')])->paginate(15);
    }

    public function show($id) {
        Admin::script("
        $('a[data-toggle=tab]').click(function(e){
            if($(e.target).html()=='司机列表') {
                location.href = location.pathname + '?showtype=driver';
            } else if($(e.target).html()=='车辆列表') {
                location.href = location.pathname + '?showtype=auto';
            } else if($(e.target).html()=='挂车列表') {
                location.href = location.pathname + '?showtype=trailer';
            }
            return false;
        });");
        return Admin::content(function (Content $content) use($id) {
            $content->header('车队管理');
            $content->description(trans('admin.view'));
            $content->row(function(Row $row) use ($id) {
                $fleetInfoBox = new Box('基本信息', Admin::grid(Fleet::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('name', '车队名称');
                    $grid->column('description', '车队描述');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $row->column(4, function(Column $column) use($fleetInfoBox) {
                    $column->append($fleetInfoBox->style('primary'));
                });

                $showtype = app('request')->get('showtype', 'driver');
                $tab = new Tab();
                $tab->add('司机列表', Admin::grid(Driver::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('fleet_id', $id)->paginate(20, ['*'], 'driverpage');
                    $grid->column('name', '姓名');
                    $grid->column('internal_num', '员工号');
                    $grid->column('status', '状态')->display(function(){
                        return array_get(Driver::$statusDict, $this->status, '未知');
                    });
                    $grid->column('mobile', '手机号码');
                    $grid->actions(function (Grid\Displayers\Actions $actions) {
                        $actions->disableDelete();
                        $actions->disableEdit();
                        $actions->append('<a href="'.admin_url('driver/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
                    });
                    $grid->disableRowSelector();
                    $grid->setView('admin.grid.simple');
                    $grid->buildCallback(function(Grid $grid){
                        $paginator = $grid->model()->eloquent();
                        if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                            $paginator->appends('showtype', 'driver');
                        }
                    });
                }), $showtype=='driver'?true:false);
                $tab->add('车辆列表', Admin::grid(Auto::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('fleet_id', $id)->paginate(20, ['*'], 'autopage');
                    $grid->column('auto_number', '车牌号码');
                    $grid->column('internal_num', '内部编号');
                    $grid->column('status', '当前状态')->display(function(){
                        return array_get(Auto::$statusDict, $this->status, '未知');
                    });
                    $grid->actions(function (Grid\Displayers\Actions $actions) {
                        $actions->disableDelete();
                        $actions->disableEdit();
                        $actions->append('<a href="'.admin_url('auto/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
                    });
                    $grid->disableRowSelector();
                    $grid->setView('admin.grid.simple');
                    $grid->buildCallback(function(Grid $grid){
                        $paginator = $grid->model()->eloquent();
                        if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                            $paginator->appends('showtype', 'auto');
                        }
                    });
                }), $showtype=='auto'?true:false);
                $tab->add('挂车列表', 'blablablabla....', $showtype=='trailer'?true:false);
                $row->column(8, function(Column $column) use($tab) {
                    $column->append($tab);
                });
            });
        });
    }

    public function grid() {
        return Admin::grid(Fleet::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->where(['company_id'=>$user['company_id']]);
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '车队名称');
            $grid->column('driver_count', '司机总数');
            $grid->column('auto_count', '车辆总数');
            $grid->column('created_at', '创建日期');
            $grid->filter(function (Filter $filter) use($user) {
                $filter->useModal();
            });
            $grid->actions(function ($actions) {
                $actions->append('<a href="'.admin_url('fleet/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
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
        return Admin::form(Fleet::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->display('id', 'ID');
            $form->hidden('company_id')->default($user['company_id']);
            $form->text('name', '车队名称')->rules('required');
            $form->textarea('description', '描述信息')->rules('required');
        });
    }

}
