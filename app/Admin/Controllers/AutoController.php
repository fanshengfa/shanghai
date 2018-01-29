<?php

namespace App\Admin\Controllers;

use DB;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Widgets\Box;
use App\Model\Auto;
use App\Model\AutoBrand;
use App\Model\AutoSerie;
use App\Model\AutoStyle;
use App\Model\Fleet;
use App\Model\Order;
use App\Model\Company;
use App\Admin\Extensions\Widgets\MapBox;

class AutoController extends Controller
{
    use ModelForm;

    public function index()
    {
        return $this->content('车辆管理', null, function (Content $content) {
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
        return $this->content('车辆管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('车辆管理', trans('admin.edit'), function (Content $content) use($id) {
            $content->body($this->form()->edit($id));
        });
    }

    public function show($id) {
        return $this->content('车辆管理', trans('admin.view'), function (Content $content) use($id) {
            $content->row(function(Row $row) use ($id) {
                $auto = (new Auto())->where('id', $id)->getModel();
                $driverInfoBox = new Box('基本信息', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->whereIn('company_id', array_keys($user['children']))->where('id', $id);
                    $grid->column('brand.name', '车辆品牌');
                    $grid->column('auto_number', '车牌号码');
                    $grid->column('internal_num', '内部编号');
                    $grid->column('net_component', '已安装的车联网模块');
                    //$grid->column('dlink_auto_number', '车联网模块终设备号');
                    $grid->column('net_rtid', '车联网模块终设备号');
                    $grid->column('production_date', '出厂日期');
                    $grid->column('sign_date', '上牌日期');
                    $grid->column('mileage', '累计公里数');
                    $grid->column('company_id', '所属公司')->display(function() use($user) {
                        return join(',', is_array($user['company_key'][$this->company_id]) ? $user['company_key'][$this->company_id] : []);
                    });
                    $grid->column('fleet.name', '所属车队');
                    $grid->column('run_status', '行驶状态')->display(function(){
                        return array_get(Auto::$runStatusDict, $this->run_status, '未知');
                    });
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $licenceInfoBox = new Box('车辆行驶证信息', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('id', $id);
                    $autoLicense = ['auto_model'=>'品牌型号','auto_vin'=>'车辆识别代号',
                        'auto_engine_no'=>'发动机号码','auto_issue_date'=>'发证日期','auto_number'=>'车牌号',
                        'auto_file_number'=>'档案编号','auto_approved_passengers_capacity'=>'核定载人数','auto_gross_mass'=>'总质量',
                        'auto_unladen_mass'=>'整备质量','auto_approved_load'=>'核定载质量',
                        'auto_traction_mass'=>'准牵引总质量','auto_comment'=>'备注',
                        'auto_inspection_record'=>'检验记录'];
                    $grid->column('auto_model', '品牌型号');
                    $grid->column('auto_vin', '车辆识别代号');
                    $grid->column('auto_engine_no', '发动机号码');
                    $grid->column('auto_issue_date', '发证日期');
                    $grid->column('auto_number', '车牌号');
                    $grid->column('auto_file_number', '档案编号');
                    $grid->column('auto_approved_passengers_capacity', '核定载人数');
                    $grid->column('auto_gross_mass', '总质量');
                    $grid->column('auto_unladen_mass', '整备质量');
                    $grid->column('auto_approved_load', '核定载质量');
                    $grid->column('auto_traction_mass', '准牵引总质量');
                    $grid->column('auto_comment', '备注');
                    $grid->column('auto_inspection_record', '检验记录');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $idInfoBox = new Box('车辆照片', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('id', $id);
                    $keys = [
                        'vehicle'  => '车辆照片',
                        'homepage' => '行驶证主页',
                        'vicepage' => '行驶证副页',
                        'test'     => '车辆检验记录'
                    ];
                    $grid->column('vehicle', '车辆照片')->image();
                    $grid->column('homepage', '行驶证主页')->image();
                    $grid->column('vicepage', '行驶证副页')->image();
                    $grid->column('test', '车辆检验记录')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $certificateInfoBox = new Box('运输证信息', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('id', $id);
                    $roadcard = ['roadcard_license_no'=>'经营许可证号','auto_cate_id'=>'车辆类型',
                        'roadcard_tseat'=>'吨(座)位','roadcard_dimensions'=>'车辆（毫米）',
                        'roadcard_issue_date'=>'发证日期','roadcard_expire_date'=>'有效期限'];
                    $grid->column('roadcard_license_no', '经营许可证号');
                    $grid->column('auto_cate_id', '车辆类型')->display(function(){
                        return array_get(Auto::$cateDict, $this->auto_cate_id, '未知');
                    });
                    $grid->column('roadcard_tseat', '吨(座)位');
                    $grid->column('w', '车辆尺寸(米)')->display(function(){
                        return sprintf('长%.2f宽%.2f高%.2f', $this->l/1000, $this->w/1000, $this->h/1000);
                    });
                    $grid->column('roadcard_issue_date', '发证日期');
                    $grid->column('roadcard_expire_date', '有效期限');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $tciInfoBox = new Box('交强险保险单（正本）信息', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('id', $id);
                    $autoLicense = ['tci_company'=>'保险公司','tci_premium'=>'保险费（合计）',
                        'tci_expire_date'=>'有效期限','tci_tax'=>'代收车船税合计'];
                    $grid->column('tci_company', '保险公司');
                    $grid->column('tci_premium', '保险费（合计）');
                    $grid->column('tci_expire_date', '有效期限');
                    $grid->column('tci_tax', '代收车船税合计');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $vciInfoBox = new Box('商业保险单（正本）信息', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('id', $id);
                    $autoLicense = ['vci_company'=>'保险公司','vci_liability'=>'第三者责任保险',
                        'vci_iop'=>'不记免赔率','vci_premium'=>'保险费合计',
                        'vci_expire_date'=>'有效期限'];
                    $grid->column('vci_company', '保险公司');
                    $grid->column('vci_liability', '第三者责任保险');
                    $grid->column('vci_iop', '不记免赔率');
                    $grid->column('vci_premium', '保险费合计');
                    $grid->column('vci_expire_date', '有效期限');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $photoInfoBox = new Box('其它照片', new Grid($auto, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('id', $id);
                    //$grid->other_photo(false)->image();
                    $grid->column('other_photo', '其它照片')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));

                $row->column(4, function(Column $column) use($driverInfoBox, $licenceInfoBox, $idInfoBox, $certificateInfoBox, $tciInfoBox, $vciInfoBox, $photoInfoBox) {
                    $column->append($driverInfoBox->solid()->style('primary'));
                    $column->append($licenceInfoBox->solid()->style('info'));
                    $column->append($idInfoBox->solid()->style('danger'));
                    $column->append($certificateInfoBox->solid()->style('warning'));
                    $column->append($tciInfoBox->solid()->style('success'));
                    $column->append($vciInfoBox->solid()->style('success'));
                    $column->append($photoInfoBox->solid()->style('default'));
                });

                $orderBox = new Box('历史运单记录', Admin::grid(Order::class, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where('auto_id', $id)->paginate(20, ['*'], 'orderpage');
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
                $auto = Auto::where('id',$id)->with(['cty'=>function($query){
                    $query->select(['serial',DB::raw('lng as `0`'),DB::raw('lat as `1`')])->limit(5000)->orderBy('time','asc');
                }])->first();
                $mapBox = new MapBox('车辆轨迹', $auto->cty->toJson());
                $row->column(8, function(Column $column) use($orderBox, $mapBox) {
                    $column->append($orderBox->solid()->style('primary'));
                    $column->append($mapBox->solid()->style('primary'));
                });
            });
        });
    }

    public function search(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return Auto::where(function($query) use ($q) {
            $query->where('auto_number', 'like', "%$q%");
        })->orWhere(function($query) use ($q) {
            $query->where('internal_num', 'like', "%$q%");
        })->select(['id', \DB::raw('auto_number as text')])->paginate(15);
    }

    public function brand(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return AutoBrand::where('alpha', $q)->select(['id', \DB::raw('`name` as `text`')])->paginate(15);
    }

    public function serie(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return AutoSerie::where('auto_brand_id', $q)->get(['id', \DB::raw('`name` as `text`')]);
    }

    public function style(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return AutoStyle::where('auto_serie_id', $q)->get(['id', \DB::raw('`name` as `text`')]);
    }

    public function grid() {
        return Admin::grid(Auto::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->whereIn('company_id', array_keys($user['children']));
            $grid->column('id', 'ID')->sortable();
            $grid->column('auto_number', '车牌号码');
            $grid->column('internal_num', '内部编号');
            $grid->column('company_id', '所属公司')->display(function() use($user) {
                return join(',', is_array($user['company_key'][$this->company_id]) ? $user['company_key'][$this->company_id] : []);
            });
            $grid->column('fleet.name', '车队');
            $grid->column('brand.name', '品牌');
            $grid->column('status', '当前状态')->display(function(){
                return array_get(Auto::$statusDict, $this->status, '未知');
            });
            $grid->column('sign_date', '上牌日期');
            $grid->actions(function(Grid\Displayers\Actions $actions){
                $actions->append('<a href="'.admin_url('auto/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
            });
            $grid->filter(function (Filter $filter) use($user) {
                $filter->useModal();
                $filter->like('internal_num', '内部编号');
                $filter->like('auto_number', '车牌号码');
                $filter->like('net_rtid', '车联网串号');
                $filter->equal('fleet.id', '所属车队')->select(Fleet::whereIn('company_id', array_keys($user['children']))->get()->pluck('name', 'id'));
                $filter->in('status', '当前状态')->select(Auto::$statusDict);
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
        return Admin::form(Auto::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->tab('基本信息', function (Form $form) use($id, $user) {
                $form->display('id', 'ID');
                $form->select('auto_brand_id', '车辆品牌')->placeholder('请输入首字母查询')->ajax(url('auto/brand'))->load('auto_serie_id', url('auto/serie'));
                //$form->select('auto_brand_id', '车辆品牌')->options(AutoBrand::all()->pluck('name', 'id'))->load('auto_serie_id', url('auto/serie'));
                $form->select('auto_serie_id', '车    系')->placeholder('选择车系')->load('auto_style_id', url('auto/style'));
                $form->select('auto_style_id', '车    型')->placeholder('选择车型');
                $form->text('auto_number', '车牌号码')->rules('required');
                $form->text('internal_num', '内部编号')->rules('required');
                $form->select('net_component', '车联网模块')->placeholder('(可选)')->options([
                    'CLINK'=>'康明斯物联网',
                    'DLINK'=>'D-link'
                ]);
                //$form->text('dlink_auto_number', '车联网模块终设备号');
                $form->text('net_rtid', '车联网模块终设备号');
                $form->date('production_date', '出厂日期')->rules('required');
                $form->date('sign_date', '上牌日期')->rules('required');
                $form->number('mileage', '累计公里数')->rules('required');
                $form->select('fleet_id', '所属车队')->options(Fleet::whereIn('company_id', array_keys($user['children']))->get()->pluck('name', 'id'));
                $form->select('company_id', '所属公司')->default($user['company_id'])->options(Company::find($user['company_id'])->where(function($query) use($id, $user) {
                    return $query->whereIn('id', array_keys($user['children']));
                })->selectOptions())->rules('nullable', []);
            })->tab('车辆行驶证信息', function (Form $form) use($id) {
                $form->text('auto_model', '品牌型号')->rules('required');
                $form->text('auto_vin', '车辆识别代号')->rules('required');
                $form->text('auto_engine_no', '发动机号码')->rules('required');
                $form->date('auto_issue_date', '发证日期')->rules('required');
                //$form->text('auto_number', '车牌号')->rules('required');
                $form->text('auto_file_number', '档案编号')->rules('required');
                $form->number('auto_approved_passengers_capacity', '核定载人数')->rules('required');
                $form->number('auto_gross_mass', '总质量')->rules('required');
                $form->number('auto_unladen_mass', '整备质量')->rules('required');
                $form->number('auto_approved_load', '核定载质量')->rules('required');
                $form->number('auto_traction_mass', '准牵引总质量')->rules('required');
                $form->textarea('auto_inspection_record', '检验记录')->rules('required');
                $form->textarea('auto_comment', '备注')->rules('required');
            })->tab('车辆照片', function (Form $form) use($id, $user) {
                $form->embeds('photo', '', function ($form) {
                    $form->image('vehicle', '车辆照片')->options($this->initialPreview())->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->rules('required');
                    $form->image('homepage', '信息主页')->options($this->initialPreview())->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->rules('required');
                    $form->image('vicepage', '信息副页')->options($this->initialPreview())->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->rules('required');
                    $form->image('test', '检验记录')->options($this->initialPreview())->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->rules('required');
                });
            })->tab('运输证信息', function (Form $form) use($id) {
                $form->text('roadcard_license_no', '经营许可证号')->rules('required');
                $form->select('auto_cate_id', '车辆类型')->options(Auto::$cateDict)->rules('required');
                $form->number('roadcard_tseat', '吨(座)位')->rules('required');
                $form->number('l', '车辆长（毫米）')->rules('required');
                $form->number('w', '车辆宽（毫米）')->rules('required');
                $form->number('h', '车辆高（毫米）')->rules('required');
                $form->date('roadcard_issue_date', '发证日期')->rules('required');
                $form->date('roadcard_expire_date', '有效期限')->rules('required');
            })->tab('交强险保险单（正本）信息', function (Form $form) use($id) {
                $form->text('tci_company', '保险公司')->rules('required');
                $form->number('tci_premium', '保险费（合计）')->rules('required');
                $form->date('tci_expire_date', '有效期限')->rules('required');
                $form->number('tci_tax', '代收车船税合计')->rules('required');
            })->tab('商业保险单（正本）信息', function (Form $form) use($id) {
                $form->text('vci_company', '保险公司')->rules('required');
                $form->text('vci_liability', '第三者责任保险')->rules('required');
                $form->text('vci_iop', '不记免赔率')->rules('required');
                $form->number('vci_premium', '保险费（合计）')->rules('required');
                $form->date('vci_expire_date', '有效期限')->rules('required');
            })->tab('其他照片', function (Form $form) use($id) {
                $form->embeds('other_photo', '', function ($form) {
                    $form->image('0', '上传其他照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview());
                    $form->image('1', '上传其他照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview());
                    $form->image('2', '上传其他照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview());
                });
            });
        });
    }

}
