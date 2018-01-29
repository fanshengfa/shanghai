<?php

namespace App\Admin\Controllers;

use Validator;
use App\Model\Driver;
use App\Model\RoutePlace;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Controllers\ModelForm;
use App\Model\Route;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Model\Partner;
use App\Model\Order;
use App\Model\OrderPlace;
use App\Model\Place;
use App\Model\Company;
use App\Model\Auto;


use Illuminate\Support\MessageBag;

use DB;

class RouteController extends Controller
{
    use ModelForm;

    public function index()
    {
        return $this->content('线路管理', null, function (Content $content) {
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
        return $this->content('线路管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('线路管理', trans('admin.edit'), function (Content $content) use ($id){
            $content->body($this->form($id)->edit($id));
        });
    }

    public function unbind($id) {
        Device::where('owner_id', $id)->update(['owner_id'=>0]);
        Login::where('user_id', $id)->update(['access_token'=>mt_rand(100000, 999999)]);
        return response()->json([
            'status'  => true,
            'message' => '成功解绑'.$id,
        ]);
    }
    
    public function search(Request $request) {
        $q = $request->get('q');
        $user = $this->user();
        return Route::where(function($query) use ($user, $q) {
            $query->where([['title', 'like', "%$q%"], ['company_id', '=', $user['company_id']]]);
        })->orWhere(function($query) use ($user, $q) {
            $query->where([['number', 'like', "%$q%"], ['company_id', '=', $user['company_id']]]);
        })->select(['id', \DB::raw('title as text')])->paginate(15);
    }

    public function show($id) {
        return $this->content('线路管理', trans('admin.view'), function (Content $content) use ($id){
            $content->row(function(Row $row) use ($id){
                $box = new Box('基本信息', Admin::grid(Route::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->whereIn('company_id', array_keys($user['children']))->where('id', $id);
                    //$grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('id', 'id')->sortable();
                    $grid->column('number', '线路编号');
                    $grid->column('title', '线路名称');
                    $grid->column('status', '当前状态')->display(function(){
                        if($this->status==1) {
                            return '执行中';
                        } elseif($this->status==0) {
                            return '结束';
                        } else {
                            return '未知';
                        }
                    })->label();
                    $grid->column('partner.name', '所属客户');
                    $grid->column('created_at', '创建时间');
                    $grid->column('start_date', '开始日期');
                    $grid->column('end_date', '结束日期');
                    $grid->column('order_count', '包含运单数量');
                    $grid->column('description', '计划总运费');
                    $grid->column('description', '实际总运费');
                    $grid->setView('admin.grid.data');
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $row->column(4, $box->solid()->style('primary'));


                $box = new Box('历史任务', Admin::grid(Order::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['route_id'=>$id]);
                    $grid->column('id', 'id')->sortable();
                    $grid->column('number', '运单编号');
                    $grid->column('custom_nums', '自定义编号');
                    $grid->column('route.title', '线路');
                    $grid->column('driver.name', '司机');

                    $grid->column('status', '运单状态')->display(function(){
                        return isset(Order::$statusDict[$this->status]) ? Order::$statusDict[$this->status] : '未知';
                    });
                    $grid->column('contact_tel', '车辆位置');
                    $grid->column('created_at', '时间')->display(function(){
                        return "创建：{$this->created_at}<br>更新：{$this->updated_at}";
                    });
                    $grid->tools(function (Grid\Tools $tools) {
                        $tools->disableRefreshButton();
                    });
                    $grid->actions(function (Grid\Displayers\Actions $actions) {
                        $actions->disableDelete();
                        $actions->disableEdit();
                        $actions->append('<a href="'.admin_url('order/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
                    });
                    $grid->setView('admin.grid.simple');
                    $grid->disableRowSelector();
                }));
                $row->column(8, $box->solid()->style('success'));
            });
        });
    }

    public function destroy($id)
    {
        if (Route::find($id)->update(['status'=>'0'])) {
            return response()->json([
                'status'  => 'success',
                'status_code' => '200',
                'message' => '删除成功！',
                'object' => null
            ]);
        } else {
            return response()->json([
                'status'  => 'error',
                'error' => [
                    'status_code' => strval("601"),
                    'message' => '删除失败！',
                ]
            ]);
        }
    }

    public function grid() {
        return Admin::grid(Route::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->whereIn('company_id', array_keys($user['children']));
            $grid->column('id', 'id')->sortable();
            $grid->column('title', '名称');
            $grid->column('status', '状态')->display(function(){
                if($this->status==1) {
                    return '执行中';
                } elseif($this->status==0) {
                    return '结束';
                } else {
                    return '未知';
                }
            });
            $grid->column('company_id', '所属公司')->display(function() use($user) {
                return join(',', is_array($user['company_key'][$this->company_id]) ? $user['company_key'][$this->company_id] : []);
            });
            $grid->column('partner.name', '所属客户');
            $grid->column('start_date', '开始时间');
            $grid->column('end_date', '结束时间');
            $grid->column('description', '描述');
            $grid->filter(function ($filter) use($user) {
                $filter->useModal();
                $filter->like('number', '项目编号');
                $filter->like('title', '名称');
                $filter->equal('status', '线路状态')->select([0=>'结束', 1=>'执行中']);
                $filter->equal('partner.id', '所属客户')->select(Partner::where(['company_id'=>$user['company_id']])->get()->pluck('name', 'id'));
            });

            $grid->actions(function ($actions) {
                $actions->append('<a href="'.admin_url('order/create?route_id='.$actions->getKey()).'"><i class="fa fa-save"></i></a>');
                $actions->append('<a href="'.admin_url('route/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
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
        /*
        return Admin::form(Route::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->hasMany('place', '途经地点', function (Form\NestedForm $form) {
                $form->map('lat', 'lng', 'map_address_p', 'map_address_c', 'map_address_r', 'map_address', '地点')->attribute('data-tk', 'll'.uniqid());
            });
        });
        */
        return Admin::form(Route::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->tab('基本信息', function (Form $form) use($id, $user) {
                //$form->hidden('company_id')->default($user['company_id']);
                $form->display('id', 'ID');
                $form->text('title', '名称')->rules('required');
                $form->select('partner_id', '客户')->options(
                    Partner::all()->pluck('name', 'id')
                );
                $form->select('company_id', '所属公司')->default($user['company_id'])->options(Company::find($user['company_id'])->where(function($query) use($id, $user) {
                    return $query->whereIn('id', array_keys($user['children']));
                })->selectOptions())->rules('nullable', []);
                $form->select('status', '状态')->options(
                    [0=>'结束', 1=>'执行中']
                );
                //$form->date('start_date', '开始时间');
                //$form->date('end_date', '结束时间');
                $form->text('guess_cost', '计划运费');
                $form->text('guess_kilometer', '计划里程');
                $form->text('guess_fuel', '计划油耗');
                $form->text('guess_weight', '计划载重');
                $form->text('description', '描述');
            })->tab('发货单位', function (Form $form) use($id) {
                $form->text('send_company', '单位名称')->rules('required');
                $form->text('send_contact_name', '联系人');
                $form->mobile('send_contact_mobile', '联系电话');
            })->tab('收货单位', function (Form $form) use($id) {
                $form->text('recevie_company', '单位名称')->rules('required');
                $form->text('recevie_contact_name', '联系人');
                $form->mobile('recevie_contact_mobile', '联系电话');
            })->tab('货品', function (Form $form) use($id) {
                $form->hasMany('goods', '货品', function (Form\NestedForm $form) {
                    $form->display('id', 'ID');
                    $form->hidden('route_id', '线路ID');
                    $form->text('gname', '名称');
                    $form->number('spec_l', '规格长(米)');
                    $form->number('spec_w', '规格宽(米)');
                    $form->number('spec_h', '规格高(米)');
                    $form->text('uweight', '件重(千克)');
                    $form->text('num', '数量（个）');
                    $form->text('tweight', '重量（千克）');
                    $form->text('distance', '运距（公里）');
                    $form->text('fee', '运费（元）');
                });
            })->tab('途经地点', function (Form $form) use($id) {
                $form->hasManyDef('place', '途经地点', function (Form\NestedForm $form) {
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
                    $form->radio('guess_start_time_type', '预计到达时间')->options([
                        '0'=>'当日', '1'=>'次日', '2'=>'第三天', '3'=>'第四天', '4'=>'第五天'
                    ])->rules('required');
                    $form->time('guess_start_time', '预计到达时间');
                    $form->radio('guess_end_time_type', '预计离开时间')->options([
                        '0'=>'当日', '1'=>'次日', '2'=>'第三天', '3'=>'第四天', '4'=>'第五天'
                    ])->rules('required');
                    $form->time('guess_end_time',   '预计离开时间');
                });
            })->tab('班线计划', function (Form $form) use($id) {
                $form->hasMany('plan', '计划', function (Form\NestedForm $form) {
                    $form->display('id', 'ID');
                    $form->date('time_base', '基准日期')->rules('required');
                    $form->radio('time_type', '时间间隔')->options([
                        '1'=>'每天', '2'=>'每两天', '3'=>'每三天', '4'=>'每四天', '5'=>'每五天'
                    ])->rules('required');
                    $form->time('create_time', '创建时间')->rules('required');

                    $form->radio('guess_start_time_type', '预计运输开始时间')->options([
                        '0'=>'当日', '1'=>'次日', '2'=>'第三天', '3'=>'第四天', '4'=>'第五天'
                    ])->rules('required');/*->rules('required|regex:/^\d+$/|min:10', [
                        'regex' => 'code必须全部为数字',
                        'min'   => 'code不能少于10个字符',
                    ]);*/
                    $form->time('guess_start_time', '预计运输开始时间')->rules('required');

                    $form->radio('guess_end_time_type', '预计运输结束时间')->options([
                        '0'=>'当日', '1'=>'次日', '2'=>'第三天', '3'=>'第四天', '4'=>'第五天'
                    ])->rules('required');
                    $form->time('guess_end_time', '预计运输结束时间')->rules('required');
                });
            })->tab('常用司机', function (Form $form) use($id, $user) {
                $form->hasMany('driver', '司机', function (Form\NestedForm $form) use($user) {
                    $form->select('driver_id', '司机姓名')->options(
                        Driver::with('company')->whereIn('company_id', array_keys($user['children']))->get()->map(function($item){
                            $item->tldesc = $item->name."({$item->internal_num},{$item->company->name})";
                            return $item;
                        })->pluck('tldesc', 'id')
                    );
                });
            })->tab('常用车辆', function (Form $form) use($id, $user) {
                $form->hasMany('auto', '车辆', function (Form\NestedForm $form) use($user) {
                    $form->select('auto_id', '车牌号码')->options(
                        Auto::with('company')->whereIn('company_id', array_keys($user['children']))->get()->map(function($item){
                            $item->tldesc = $item->auto_number;
                            return $item;
                        })->pluck('tldesc', 'id')
                    );
                });
            });
            $form->saving(function (Form $form) {
                $messageBag = new MessageBag();
                $validator = Validator::make([],[]);
                foreach ($form->plan as $key=>$plan) {
                    if($plan['guess_start_time_type']>$plan['guess_end_time_type']) {
                        $validator->errors()->add("plan.{$key}.guess_start_time_type", "时间先后顺序错误");
                        $validator->errors()->add("plan.{$key}.guess_end_time_type", "时间先后顺序错误");
                        $messageBag = $messageBag->merge($validator->messages());
                        return back()->withInput()->withErrors($messageBag);
                    } elseif($plan['guess_start_time_type']==$plan['guess_end_time_type'] && $plan['guess_start_time']>=$plan['guess_end_time']) {
                        $validator->errors()->add("plan.{$key}.guess_start_time", "时间先后顺序错误");
                        $validator->errors()->add("plan.{$key}.guess_end_time", "时间先后顺序错误");
                        $messageBag = $messageBag->merge($validator->messages());
                        return back()->withInput()->withErrors($messageBag);
                    }
                }
            });

            $form->saved(function(Form $form){
                /*
                DB::update("update route_place t1, place t2 set t1.place_address_p_id=t2.address_p_id, t1.place_address_c_id=t2.address_c_id, t1.place_address_r_id=t2.address_r_id, t1.place_address=t2.address, t1.place_polygon=t2.polygon, t1.address=t2.address, t1.polygon=t2.polygon where t1.place_id=t2.id and t1.route_id={$form->model()->id};");
                DB::update("update route_place t1, area t2 set t1.address_p=t2.`name` where t1.place_address_p_id=t2.id and t1.route_id={$form->model()->id};");
                DB::update("update route_place t1, area t2 set t1.address_c=t2.`name` where t1.place_address_c_id=t2.id and t1.route_id={$form->model()->id};");
                DB::update("update route_place t1, area t2 set t1.address_r=t2.`name` where t1.place_address_r_id=t2.id and t1.route_id={$form->model()->id};");
                */
                $request = app('request');
                $place = $request->get('place', []);
                if($place && $request->getMethod()=='PUT') {
                    $updateRaw = function() use ($place){
                        $place = array_values($place);
                        array_walk($place, function(&$item, $key){
                            $item = "when {$item['id']} then {$key}";
                        });
                        return DB::raw('case id '.join(' ', $place).' end');
                    };
                    RoutePlace::whereIn('id', array_keys($place))->update(['order'=>call_user_func($updateRaw)]);
                }
                $routePlace = RoutePlace::with('place')->where('route_id', $form->model()->id)->get()->map(function($item){
                    $item->place_name = $item->place->name;
                    return $item;
                })->pluck('place_name', 'id')->toArray();
                $form->model()->update(['title'=>join('-', $routePlace)]);
            });
        });
    }

}