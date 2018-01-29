<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Model\Route;
use App\Model\RouteGoods;
use App\Model\Order;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid\Filter;
use App\Model\Card;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Model\Driver;
use App\Model\Company;
use App\Model\Fleet;

class DriverController extends Controller
{
    use ModelForm;

    public function index()
    {
        return $this->content('司机管理', null, function (Content $content) {
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
        return $this->content('司机管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('司机管理', trans('admin.edit'), function (Content $content) use($id) {
            $content->body($this->form($id)->edit($id));
        });
    }

    public function show($id) {
        return $this->content('司机管理', trans('admin.view'), function (Content $content) use($id) {
            $content->row(function(Row $row) use ($id) {
                $driver = (new Driver())->where('id', $id)->getModel();
                $driverInfoBox = new Box('基本信息', new Grid($driver, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('internal_num', '员工编号');
                    $grid->column('mobile', '手机号码');
                    $grid->column('email', '邮箱地址');
                    $grid->column('description', '备注信息');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $licenceInfoBox = new Box('驾驶证信息', new Grid($driver, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('licence_type', '驾驶证类型');
                    $grid->column('licence_num', '驾驶证号');
                    $grid->column('licence_register_date', '注册日期');
                    $grid->column('licence_expired_date', '截止日期');
                    $grid->column('licence_firstday', '初次领证日期');
                    $grid->column('licence_archives_no', '档案编号');
                    $grid->column('licence_desc', '记录');
                    $grid->column('licence_photo_front', '司机驾驶证照片')->image();
                    $grid->column('licence_photo_back', '司机驾驶证照片')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $idInfoBox = new Box('身份证信息', new Grid($driver, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('name', '姓名');
                    $grid->column('idnumber', '身份证号');
                    $grid->column('idnation', '民族');
                    $grid->column('idaddress', '住址');
                    $grid->column('idexpire', '有效期限');
                    $grid->column('idnumber_photo_front', '司机身份证照片')->image();
                    $grid->column('idnumber_photo_back', '司机身份证照片')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $certificateInfoBox = new Box('司机从业资格信息', new Grid($driver, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('certificate_firstday', '初次领证日期');
                    $grid->column('certificate_expired_date', '有效期');
                    $grid->column('certificate_department', '发证机关');
                    $grid->column('certificate_photo_front', '从业资格证照片（首页）')->image();
                    $grid->column('certificate_photo_back', '从业资格证照片（副页）')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $workInfoBox = new Box('工作相关', new Grid($driver, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('fleet.name', '所属车队');
                    $grid->column('auto.auto_number', '常用车辆');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $photoInfoBox = new Box('其它照片', new Grid($driver, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    //$grid->other_photo(false)->image();
                    $grid->column('other_photo', '其它照片')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));

                $row->column(4, function(Column $column) use($driverInfoBox, $licenceInfoBox, $idInfoBox, $certificateInfoBox, $workInfoBox, $photoInfoBox) {
                    $column->append($driverInfoBox->solid()->style('primary'));
                    $column->append($licenceInfoBox->solid()->style('info'));
                    $column->append($idInfoBox->solid()->style('danger'));
                    $column->append($certificateInfoBox->solid()->style('warning'));
                    $column->append($workInfoBox->solid()->style('success'));
                    $column->append($photoInfoBox->solid()->style('default'));
                });

                $orderBox = new Box('历史运单记录', Admin::grid(Order::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('driver_id', $id)->paginate(20, ['*'], 'orderpage');
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

    public function search(Request $request) {
        $q = $request->get('q');
        $user = $this->user();
        return Driver::where(function($query) use ($user, $q) {
            $query->where([['name', 'like', "%$q%"], ['company_id', '=', $user['company_id']]]);
        })->orWhere(function($query) use ($user, $q) {
            $query->where([['internal_num', 'like', "%$q%"], ['company_id', '=', $user['company_id']]]);
        })->orWhere(function($query) use ($user, $q) {
            $query->where([['mobile', 'like', "%$q%"], ['company_id', '=', $user['company_id']]]);
        })->select(['id', \DB::raw('name as text')])->paginate(15);
    }

    public function grid() {
        return Admin::grid(Driver::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->whereIn('company_id', array_keys($user['children']));
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '姓名');
            $grid->column('internal_num', '员工号');
            $grid->column('status', '状态')->display(function(){
                return array_get(Driver::$statusDict, $this->status, '未知');
            });
            $grid->column('mobile', '手机号码');
            $grid->column('company_id', '所属公司')->display(function() use($user) {
                //return print_r($user, true).print_r($user['company_key'][$this->company_id], true);
                return join(',', is_array($user['company_key'][$this->company_id]) ? $user['company_key'][$this->company_id] : []);
            });
            $grid->column('fleet.name', '所属车队');
            $grid->column('auto.auto_number', '当前驾驶车辆');
            $grid->filter(function (Filter $filter) use($user) {
                $filter->useModal();
                $filter->like('name', '姓名');
                $filter->like('internal_num', '员工号');
                $filter->like('mobile', '手机号码')->mobile();
                $filter->equal('fleet.id', '所属车队')->select(Fleet::where(['company_id'=>$user['company_id']])->get()->pluck('name', 'id'));
                $filter->equal('auto.id', '当前驾驶的车辆')->select(url('auto/search'))->placeholder('请输入车牌号/内部号查询');
                $filter->in('status', '当前状态')->select(Driver::$statusDict);
            });
            $grid->actions(function(Grid\Displayers\Actions $actions){
                $actions->append('<a href="'.admin_url('driver/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
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
        return Admin::form(Driver::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->tab('基本信息', function (Form $form) use($id, $user) {
                $form->display('id', 'ID');
                $form->text('internal_num', '员工编号')->rules('required');
                $form->select('company_id', '所属公司')->default($user['company_id'])->options(Company::find($user['company_id'])->where(function($query) use($id, $user) {
                    return $query->whereIn('id', array_keys($user['children']));
                })->selectOptions())->rules('nullable', []);
                $form->mobile('mobile', '手机号码')->rules('required');
                $form->password('pwd', '初始密码')->rules('required');
                $form->email('email', '邮箱地址')->placeholder('邮箱地址(可选)');
                $form->text('description', '备注信息')->placeholder('备注信息(可选)');
            })->tab('身份证信息', function (Form $form) use($id) {
                $form->text('name', '姓名')->placeholder('司机姓名')->rules('required');
                $form->mobile('mobile', '手机号码')->rules('required');
                $form->text('idnumber', '身份证号')->rules('required');
                $form->select('idnation', '民族')->options(Driver::$idnationDict)->rules('required');
                $form->text('idaddress', '住址')->rules('required');
                $form->date('idexpire', '有效期限')->rules('required');
                $form->image('idnumber_photo_front', '身份证照片（正面）')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->rules('required');
                $form->image('idnumber_photo_back', '身份证照片（背面）')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->rules('required');
            })->tab('工作相关', function (Form $form) use($id, $user) {
                $form->select('fleet_id', '所属车队')->options(Fleet::where(['company_id'=>$user['company_id']])->get()->pluck('name', 'id'));
                $form->select('auto_id', '常用车辆')->placeholder('请输入车牌号/内部号查询')->ajax(url('auto/search'));
            })->tab('驾驶证信息', function (Form $form) use($id) {
                $form->select('licence_type', '准驾车型')->options([
                    "A1"=>"大型客车","A2"=>"牵引车","A3"=>"城市公交车",
                    "B1"=>"中型客车","B2"=>"大型货车",
                    "C1"=>"小型汽车","C2"=>"小型自动挡汽车","C3"=>"低速载货汽车","C4"=>"三轮汽车"
                ])->rules('required');
                $form->text('licence_num', '驾驶证号')->rules('required');
                $form->date('licence_register_date', '注册日期')->rules('required');
                $form->date('licence_expired_date', '截止日期')->rules('required');
                $form->date('licence_firstday', '初次领证日期')->rules('required');
                $form->text('licence_archives_no', '档案编号')->rules('required');
                $form->text('licence_desc', '记录')->rules('required');
                $form->image('licence_photo_front', '驾驶证照片（正本）')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->rules('required');
                $form->image('licence_photo_back', '驾驶证照片（副本）')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->rules('required');
            })->tab('司机从业资格信息', function (Form $form) use($id) {
                $form->date('certificate_firstday', '初次领证日期')->rules('required');
                $form->date('certificate_expired_date', '有效期限')->rules('required');
                $form->text('certificate_department', '发证机关')->rules('required');
                $form->image('certificate_photo_front', '从业资格证照片（首页）')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->rules('required');
                $form->image('certificate_photo_back', '从业资格证照片（副页）')->dir(function(Form $form){
                    return 'upload/'.$form->model()->getTable();
                })->rules('required');
            })->tab('其他照片', function (Form $form) use($id) {
                $form->embeds('other_photo', '', function ($form) {
                    $form->image('0', '上传其他照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    });
                    $form->image('1', '上传其他照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    });
                    $form->image('2', '上传其他照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    });
                });
            });
            $form->saving(function (Form $form) {
                if ($form->pwd && $form->model()->pwd != $form->pwd) {
                    $form->pwd = bcrypt($form->pwd);
                }
            });
        });
    }
}
