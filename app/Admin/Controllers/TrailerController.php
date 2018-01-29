<?php

namespace App\Admin\Controllers;

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
use App\Model\Trailer;

class TrailerController extends Controller
{
    use ModelForm;

    public function index()
    {
        return $this->content('挂车管理', null, function (Content $content) {
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
        return $this->content('挂车管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('挂车管理', trans('admin.edit'), function (Content $content) use($id) {
            $content->body($this->form($id)->edit($id));
        });
    }

    public function show($id) {
        return $this->content('挂车管理', trans('admin.view'), function (Content $content) use($id) {
            $content->row(function(Row $row) use ($id) {
                $trailer = (new Trailer())->where('id', $id)->getModel();
                $baseInfoBox = new Box('基本信息', new Grid($trailer, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('internal_num', '内部编号');
                    $grid->column('fleet.name', '所属车队');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $licenceInfoBox = new Box('挂车行驶证信息', new Grid($trailer, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
                    $grid->column('trailer_type', '车辆类型');
                    $grid->column('trailer_owner', '所有人');
                    $grid->column('trailer_address', '住址');
                    $grid->column('trailer_use_character', '使用性质');
                    $grid->column('trailer_model', '品牌型号');
                    $grid->column('trailer_vin', '车辆识别代号');
                    $grid->column('trailer_issue_date', '发证日期');
                    $grid->column('trailer_number', '号牌号码');
                    $grid->column('trailer_file_number', '档案编号');
                    $grid->column('trailer_gross_mass', '总质量');
                    $grid->column('trailer_unladen_mass', '整备质量');
                    $grid->column('trailer_approved_load', '核定载质量');
                    $grid->column('trailer_traction_mass', '准牵引总质量');
                    $grid->column('l', '外廓尺寸（米）长');
                    $grid->column('w', '外廓尺寸（米）宽');
                    $grid->column('h', '外廓尺寸（米）高');
                    $grid->column('trailer_inspection_record', '检验记录');
                    $grid->column('trailer_comment', '备注');
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));
                $photoInfoBox = new Box('车辆照片', new Grid($trailer, function(Grid $grid) use($id) {
                    $user = $this->user();
                    $grid->model()->where(['company_id'=>$user['company_id']])->where('id', $id);
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
                    $grid->column('photo', '车辆照片')->image();
                    $grid->setView('admin.grid.data');
                    $grid->disablePagination();
                    $grid->disableActions();
                    $grid->disableRowSelector();
                }));

                $row->column(4, function(Column $column) use($baseInfoBox, $licenceInfoBox, $photoInfoBox) {
                    $column->append($baseInfoBox->solid()->style('primary'));
                    $column->append($licenceInfoBox->solid()->style('info'));
                    $column->append($photoInfoBox->solid()->style('danger'));
                });
            });
        });
    }

    public function grid() {
        return Admin::grid(Trailer::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->whereIn('company_id', array_keys($user['children']));
            $grid->column('id', 'ID')->sortable();
            $grid->column('trailer_number', '车牌号码');
            $grid->column('internal_num', '内部编号');
            $grid->column('company_id', '所属公司')->display(function() use($user) {
                return join(',', is_array($user['company_key'][$this->company_id]) ? $user['company_key'][$this->company_id] : []);
            });
            $grid->column('fleet.name', '车队');
            $grid->column('status', '当前状态')->display(function(){
                return array_get(Trailer::$statusDict, $this->status, '未知');
            });

            $grid->actions(function(Grid\Displayers\Actions $actions){
                $actions->append('<a href="'.admin_url('trailer/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
            });
            $grid->filter(function (Filter $filter) use($user) {
                $filter->useModal();
                $filter->like('internal_num', '内部编号');
                $filter->like('trailer_number', '车牌号码');
                $filter->equal('fleet.id', '所属车队')->select(Fleet::where(['company_id'=>$user['company_id']])->get()->pluck('name', 'id'));
                $filter->in('status', '当前状态')->select(Trailer::$statusDict);
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
        return Admin::form(Trailer::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->tab('基本信息', function (Form $form) use($id, $user) {
                $form->display('id', 'ID');
                $form->hidden('company_id')->default($user['company_id']);
                $form->text('internal_num', '内部编号');
                $form->select('fleet_id', '所属车队')->options(Fleet::where(['company_id'=>$user['company_id']])->get()->pluck('name', 'id'));
            })->tab('挂车行驶证信息', function (Form $form) use($id) {
                $form->text('trailer_type', '车辆类型')->rules('required');
                $form->text('trailer_owner', '所有人')->rules('required');
                $form->text('trailer_address', '住址')->rules('required');
                $form->text('trailer_use_character', '使用性质')->rules('required');
                $form->text('trailer_model', '品牌型号')->rules('required');
                $form->number('trailer_vin', '车辆识别代号')->rules('required');
                $form->date('trailer_issue_date', '发证日期')->rules('required');
                $form->number('trailer_number', '号牌号码')->rules('required');
                $form->number('trailer_file_number', '档案编号')->rules('required');
                $form->number('trailer_gross_mass', '总质量')->rules('required');
                $form->number('trailer_unladen_mass', '整备质量')->rules('required');
                $form->number('trailer_approved_load', '核定载质量')->rules('required');
                $form->number('trailer_traction_mass', '准牵引总质量')->rules('required');
                $form->textarea('l', '外廓尺寸（米）长')->rules('required');
                $form->textarea('w', '外廓尺寸（米）宽')->rules('required');
                $form->textarea('h', '外廓尺寸（米）高')->rules('required');
                $form->textarea('trailer_inspection_record', '检验记录')->rules('required');
                $form->textarea('trailer_comment', '备注')->rules('required');
            })->tab('挂车照片', function (Form $form) use($id, $user) {
                $form->embeds('photo', '', function ($form) {
                    $form->image('vehicle', '车辆照片')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview())->rules('required');
                    $form->image('homepage', '信息主页')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview())->rules('required');
                    /*
                    $form->file('homepage', '信息主页')->options(['initialPreviewConfig'=>[
                        ['type'=>'video', 'filetype'=>"video/mp4"]
                    ]])->rules('required');
                    */
                    $form->image('vicepage', '信息副页')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview())->rules('required');
                    $form->image('test', '检验记录')->dir(function(Form $form){
                        return 'upload/'.$form->model()->getTable();
                    })->options($this->initialPreview())->rules('required');
                });
            });
        });
    }

}
