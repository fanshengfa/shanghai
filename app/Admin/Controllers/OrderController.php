<?php

namespace App\Admin\Controllers;

use App\Model\OrderLog;
use App\Model\OrderPlace;
use App\Model\Route;
use App\Model\RouteGoods;
use App\Model\RoutePlace;
use App\Model\RoutePlan;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Widgets\Box;
use App\Model\Card;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Model\Order;
use App\Model\Driver;
use App\Model\Overdraft;
use App\Model\Company;
use App\Model\Auto;
use App\Model\Place;

class OrderController extends Controller
{
    use ModelForm;

    public function index()
    {
        $request = app('request');
        $path = $request->path();
        $whfun = null;
        $header = '';
        $description = '';
        if($path == 'order/verify') {
            $whfun = function($query) {
                $query->where('status', 20);
            };
            $header = '运单审核';
        } elseif($path == 'order/doing') {
            $whfun = function($query) {
                $query->whereIn('status', Order::$executingStatus);
            };
            $header = '执行中的运单管理';
            $description = '当前待执行&执行中的运单';
        } elseif($path == 'order/history') {
            $whfun = function($query) {
                $query->whereIn('status', Order::$historyStatus);
            };
            $header = '历史记录';
            $description = '已经执行完毕的运单历史记录';
        } elseif($path == 'order/check') {
            $whfun = function($query) {
                $query->whereIn('status', Order::$checkStatus);
            };
            $header = '待核销记录';
            $description = '待核销运单记录';
        } else {
            $header = '运单管理';
        }
        return $this->content($header, $description, function (Content $content) use($whfun) {
            $content->body($this->grid($whfun));
        });
    }

    public function verify() {
        return $this->content('运单审核', null, function (Content $content) {
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
        return $this->content('运单管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('运单管理', trans('admin.edit'), function (Content $content) use ($id){
            $content->body($this->form($id)->edit($id));
        });
    }

    public function show($id) {
        $order = Order::with('auto')->where(['id'=>$id])->whereIn('status', Order::$executingStatus)->first();
        if($order) {
            Admin::script('AMap.service([], function () {
                var map = new AMap.Map("containerId", {
                    resizeEnable: true,
                    scrollWheel: false,
                    zoom:11,
                    center: ['.$order->auto->lng.', '.$order->auto->lat.']
                });
                var marker = new AMap.Marker({});
                marker.setIcon("/assets/image/truck-map-marker.png")
                marker.setPosition(['.$order->auto->lng.', '.$order->auto->lat.']);
                marker.setMap(map);
            });');
        } else {
            Admin::script('$("#containerId").closest(".box").remove()');
        }
        return $this->content('运单管理', trans('admin.view'), function (Content $content) use ($id){
            $content->row(function(Row $row) use ($id){
                $orderInfoBox = new Box('运单信息', Admin::grid(Order::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('id', '运单ID')->sortable();
                    $grid->column('number', '运单编号');
                    $grid->column('route.title', '所属项目');
                    $grid->column('partner.name', '所属客户');
                    $grid->column('status', '当前状态')->display(function(){
                        return isset(Order::$statusDict[$this->status]) ? Order::$statusDict[$this->status] : '未知';
                    })->label();
                    $grid->column('created_at', '创建时间');
                    $grid->column('guess_start_time', '计划开始时间');
                    $grid->column('guess_end_time', '计划结束时间');
                    $grid->column('dispatcher.name', '调度员');
                    $grid->column('kilometer', '里程（千米）');
                    $grid->column('fuel', '油耗（升）');
                    $grid->column('guess_weight', '计划载重 （单位吨）');
                    $grid->column('guess_cost', '计划运费 （元）');
                    $grid->setView('admin.grid.data');
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));

                $drauInfoBox = new Box('司机&车辆信息', Admin::grid(Order::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('driver.name', '主司机');
                    $grid->column('auto.auto_number', '运输车辆');
                    $grid->setView('admin.grid.data');
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));

                $placeInfoBox = new Box('装卸', Admin::grid(OrderPlace::class, function(Grid $grid) use($id) {
                    $grid->model()->where(['order_id'=>$id]);
                    $grid->column('')->display(function(){
                        $label = 'label-success';
                        if ($this->status == '0') {
                            $label = 'label-default';
                        } else if ($this->status == '-1') {
                            $label = 'label-danger';
                        }
                        $task_type_name = OrderPlace::$taskTypeDict[$this->task_type];
                        $status_name = OrderPlace::$statusDict[$this->status];
                        return "
                        <ul class='nav nav-stacked'>
                            <li>
                                <span class='label {$label}'>{$task_type_name}&nbsp;{$status_name}</span>
                                <br>
                                <span style='margin-right:10px'>{$this->address_p}</span>
                                <span style='margin-right:10px'>{$this->address_c}</span>
                                <span style='margin-right:10px'>{$this->address_r}</span>
                                {$this->address}
                            </li>
                        </ul>";
                    });
                    $grid->setView('admin.grid.simple');
                    $grid->disableActions();
                    $grid->disableRowSelector();
                    $grid->disablePagination();
                }));

                $row->column(4, function(Column $column) use ($orderInfoBox, $drauInfoBox, $placeInfoBox){
                    $column->append($orderInfoBox->style('primary'));
                    $column->append($drauInfoBox->style('default'));
                    $column->append($placeInfoBox->style('warning'));
                });

                $orderAddressBox = new Box('当前地点', '<div id="containerId" style="width:100%;height:300px;"></div>');
                $orderHistoryBox = new Box('运单历史', Admin::grid(OrderLog::class, function(Grid $grid) use($id) {
                    $grid->model()->where(['order_id'=>$id])->paginate(15, ['*'], 'historypage');
                    $grid->column('id', 'id')->sortable();
                    $grid->column('driver_id', '司机');
                    $grid->column('auto_id', '车牌号');
                    $grid->column('is_place_log', '事件')->display(function(){
                        if($this->is_place_log) {
                            $place = json_decode($this->description, true);
                            return "地点:{$place['address']},状态:".array_get(OrderPlace::$statusDict, $place['status'], '未知');
                        } else {
                            return array_get(Order::$statusDict, $this->status, '未知');
                        }
                    });
                    $grid->column('remark', '捎句话');
                    $grid->column('photo', '图片')->image();
                    $grid->column('created_at', '更新时间');
                    $grid->tools(function (Grid\Tools $tools) {
                        $tools->disableRefreshButton();
                    });
                    $grid->disableActions();
                    $grid->option('disableBoxheader', true);
                    $grid->disableFilter();
                    $grid->disableRowSelector();
                    $grid->disableExport();
                    $grid->disableCreation();
                }));
                $row->column(8, function(Column $column) use ($orderAddressBox, $orderHistoryBox){
                    $column->append($orderAddressBox->style('danger'));
                    $column->append($orderHistoryBox->style('success'));
                });
            });
        });
    }

    public function grid(\Closure $whfun=null) {
        return Admin::grid(Order::class, function(Grid $grid) use($whfun) {
            $user    = $this->user();
            $grid->model()->where(function($query) use ($whfun) {
                $whfun and call_user_func($whfun, $query);
            })->whereIn('company_id', array_keys($user['children']))->orderBy('id', 'desc');
            $grid->column('id', 'id')->sortable();
            $grid->column('number', '运单编号');
            $grid->column('custom_nums', '自定义编号');
            $grid->column('route.title', '线路');
            $grid->column('driver.name', '司机');

            $grid->column('status', '运单状态')->display(function(){
                return isset(Order::$statusDict[$this->status]) ? Order::$statusDict[$this->status] : '未知';
            });
            $grid->column('company_id', '所属公司')->display(function() use($user) {
                return join(',', is_array($user['company_key'][$this->company_id]) ? $user['company_key'][$this->company_id] : []);
            });
            //$grid->column('contact_tel', '车辆位置');
            $grid->column('created_at', '时间')->display(function(){
                return "创建：{$this->created_at}<br>更新：{$this->updated_at}";
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->useModal();
                $filter->between('created_at', '创建时间')->datetime();
                $filter->between('updated_at', '更新时间')->datetime();
                $filter->like('number', '运单编号');
                $filter->equal('route_id', '所属线路')->select(admin_url('route/search'))->placeholder('请输入线路名称/编号查询');
                $filter->equal('driver_id', '承运司机')->select(admin_url('driver/search'))->placeholder('请输入司机姓名/员工号/手机号查询');
                $filter->equal('auto_id', '承运车辆')->select(admin_url('auto/search'))->placeholder('请输入车牌号/内部号查询');
                //$filter->in('status', '状态')->multipleSelect(Order::$statusDict);
            });

            $grid->actions(function ($actions) {
                $actions->append('<a href="'.admin_url('order/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
            });

            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->disableCreation();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form($id='')
    {
        return Admin::form(Order::class, function (Form $form) use($id) {
            $user = $this->user();
            $route_id = app('request')->get('route_id');
            if(empty($route_id)) {
                $order = Order::find($id);
                $route_id = $order->route_id;
            }
            $route = Route::find($route_id)->toArray();
            $plan  = RoutePlan::where(['route_id'=>$route_id])->where(function($query){
                $query->whereRaw('(curdate()-time_base)%time_type=0 and curtime()>create_time');
            })->selectRaw('
                *, curdate() curdate, curtime() curtime, 
                curdate() guess_start_date,
                date_add(curdate(), interval `guess_end_time_type` day) guess_end_date')->orderBy('create_time', 'asc')->first();
            $form->tab('基本信息', function (Form $form) use($id, $route_id, $user, $route, $plan) {
                $form->display('id', 'ID');
                $form->hidden('partner_id')->default($route['partner_id']);
                $form->hidden('dispatcher_id')->default($user['id']);
                $form->select('route_id', '所属线路')->default($route_id)->addElementClass('route_id')->options(function(){
                    return Route::all()->map(function($item){
                        $item->tldesc = $item->title."($item->number)";
                        return $item;
                    })->pluck('tldesc', 'id');
                })->rules('required');
                $form->select('company_id', '所属公司')->default($user['company_id'])->options(Company::find($user['company_id'])->where(function($query) use($id, $user) {
                    return $query->whereIn('id', array_keys($user['children']));
                })->selectOptions())->rules('nullable', []);
                Admin::script('$(".route_id").on("select2:select",function(){
                    var data = $(this).val();
                    location.href="/'.app('request')->path().'?route_id="+data;
                });');
                $form->select('status', '运单状态')->placeholder('请选择运单状态')->options(Order::$statusDict);
                $form->select('driver_id', '承运主司机')->placeholder('请选择承运主司机')->options(Driver::whereIn('company_id', array_keys($user['children']))->get()->map(function($item){
                    $item->tldesc = $item->name."($item->mobile)";
                    return $item;
                })->pluck('tldesc', 'id'))->rules('required');
                $form->select('auto_id', '承运车辆')->placeholder('请输入车牌号/内部号查询')->options(Auto::whereIn('company_id', array_keys($user['children']))->get()->map(function($item){
                    $item->tldesc = $item->auto_number."($item->internal_num)";
                    return $item;
                })->pluck('tldesc', 'id'))->rules('required');
                $form->datetime('guess_start_time', '计划运输开始时间')->default(function() use($id, $plan) {
                    return $id ? '' : $plan['guess_start_date'].' '.$plan['guess_start_time'];
                })->rules('required');
                $form->datetime('guess_end_time', '计划运输完成时间')->default(function() use($id, $plan) {
                    return $id ? '' : $plan['guess_end_date'].' '.$plan['guess_end_time'];
                })->rules('required');
                $form->number('guess_kilometer', '计划里程（单位公里）')->default($route['guess_kilometer']);
                $form->number('guess_fuel', '计划油耗（单位升）')->default($route['guess_fuel']);
                $form->number('guess_weight', '计划载重（单位吨）')->default($route['guess_weight']);
                $form->number('guess_cost', '计划运费（元）')->default($route['guess_cost']);
            })->tab('发货单位', function (Form $form) use($id, $route) {
                $form->text('send_company', '单位名称')->default($route['send_company'])->rules('required');
                $form->text('send_contact_name', '联系人')->default($route['send_contact_name'])->rules('required');
                $form->mobile('send_contact_mobile', '联系电话')->default($route['send_contact_mobile'])->rules('required');
            })->tab('收货单位', function (Form $form) use($id, $route) {
                $form->text('recevie_company', '单位名称')->default($route['recevie_company'])->rules('required');
                $form->text('recevie_contact_name', '联系人')->default($route['recevie_contact_name'])->rules('required');
                $form->mobile('recevie_contact_mobile', '联系电话')->default($route['recevie_contact_mobile'])->rules('required');
            })->tab('货品', function (Form $form) use($id, $route_id) {
                $form->hasManyDef('goods', '货品', function (Form\NestedForm $form) {
                    $form->display('id', 'ID');
                    //$form->hidden('route_id', '线路ID');
                    $form->text('gname', '名称');
                    $form->text('spec_l', '规格长');
                    $form->text('spec_w', '规格宽');
                    $form->text('spec_h', '规格高');
                    $form->text('uweight', '件重(千克)');
                    $form->text('num', '数量（个）');
                    $form->text('tweight', '重量（千克）');
                    $form->text('distance', '运距（公里）');
                    $form->text('fee', '运费（元）');
                })->default($id ? [] : RouteGoods::where(['route_id'=>$route_id])->get()->toArray());
            })->tab('途经地点', function (Form $form) use($id, $route_id, $plan) {
                $default = RoutePlace::where(['route_id'=>$route_id])->orderBy('order', 'asc')->orderBy('id', 'asc')->get()->toArray();
                array_walk($default, function(&$item, $key) use($plan) {
                    $item['guess_start_time'] = date('Y-m-d H:i:s',strtotime("{$plan['guess_start_date']} {$item['guess_start_time']} +{$item['guess_start_time_type']} day"));
                    $item['guess_end_time']   = date('Y-m-d H:i:s',strtotime("{$plan['guess_start_date']} {$item['guess_end_time']} +{$item['guess_start_time_type']} day"));
                });
                $form->hasManyDef('place', '途经地点', function (Form\NestedForm $form) use($id, $route_id, $plan) {
                    $form->display('id', 'ID');
                    $form->radio('task_type', '装卸货')->options(
                        OrderPlace::$taskTypeDict
                    );
                    $form->select('place_id', '中转站')->options(
                        Place::with(['province', 'city', 'region'])->get()->map(function($item){
                            $item->tldesc = $item->name."(".$item->province->name.','.$item->city->name.",{$item->region->name})";
                            return $item;
                        })->pluck('tldesc', 'id')
                    );
                    $form->datetime('guess_start_time', '预计到达时间')->rules('required');
                    $form->datetime('guess_end_time',   '预计离开时间')->rules('required');
                    $form->hidden('order', '排序')->rules('required');
                })->default($id ? [] : $default);
                //})->options(['add'=>false, 'remove'=>false])->default($id ? [] : $default);
            })->tab('预支项', function (Form $form) use($id, $user) {
                $form->checkboxInput('overdraft', '项目')->options(
                    Overdraft::where(['status'=>0,'company_id'=>$user['company_id']])->get()->map(function($item){
                        $item->tldesc = "{$item->name}，单位({$item->unit})";
                        return $item;
                    })->pluck('tldesc', 'id')
                )->withPivot(['value']);
            })->tab('预支卡', function (Form $form) use($id, $user) {
                $form->checkbox('card', '卡')->options(
                    Card::where(['card_status'=>0,'company_id'=>$user['company_id']])->get()->map(function($item){
                        $item->tldesc = "卡的类型：".($item->card_type==1?'燃油卡':'路桥卡').", 卡号：{$item->card_number}, 金额：{$item->balance}";
                        return $item;
                    })->pluck('tldesc', 'id')
                )->stacked();
            });
            $form->tools(function($tool){
                $tool->disableListButton();
            });
        });
    }
}
