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
use App\Model\Place;
use App\Model\AutoBrand;
use App\Model\AutoSerie;
use App\Model\AutoStyle;
use App\Model\Fleet;
use App\Model\Area;

class PlaceController extends Controller
{
    use ModelForm;

    public function index()
    {
        return $this->content('中转站管理', null, function (Content $content) {
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
        return $this->content('中转站管理', trans('admin.create'), function (Content $content) {
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return $this->content('中转站管理', trans('admin.edit'), function (Content $content) use($id) {
            $content->body($this->form($id)->edit($id));
        });
    }

    public function search(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return Place::where(function($query) use ($q) {
            $query->where('auto_number', 'like', "%$q%");
        })->orWhere(function($query) use ($q) {
            $query->where('internal_num', 'like', "%$q%");
        })->select(['id', \DB::raw('auto_number as text')])->paginate(15);
    }

    public function city(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return Area::where('parent_id', $q)->get(['id', \DB::raw('`name` as `text`')]);
    }

    public function region(\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        return Area::where('parent_id', $q)->get(['id', \DB::raw('`name` as `text`')]);
    }

    public function grid() {
        return Admin::grid(Place::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->where(['company_id'=>$user['company_id']]);
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '名称');
            $grid->column('province.name', '省份');
            $grid->column('city.name', '城市');
            $grid->column('region.name', '区县');
            $grid->column('address', '地址')->style('width:180px');
            $grid->column('polygon', '围栏')->style('width:300px');
            $grid->actions(function(Grid\Displayers\Actions $actions){
                $actions->append('<a href="'.admin_url('place/'.$actions->getKey()).'"><i class="fa fa-eye"></i></a>');
            });
            $grid->filter(function (Filter $filter) use($user) {
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
        return Admin::form(Place::class, function (Form $form) use($id) {
            $user = $this->user();
            $place = $id ? Place::find($id) : new \stdClass();
            $form->display('id', 'ID');
            $form->hidden('company_id')->default($user['company_id']);
            $form->text('name', '名称')->rules('required');
            $form->select('address_p_id', '省份')->placeholder('选择省份')->options(
                ['-1'=>'选择省份'] + Area::where('level_type',1)->get()->pluck('name', 'id')->toArray()
            )->load('address_c_id', url('place/city'));
            $form->select('address_c_id', '城市')->placeholder('选择城市')->options(
                $place->address_p_id ? ['-1'=>'选择城市'] + Area::where(['level_type'=>2, 'parent_id'=>$place->address_p_id])->get()->pluck('name', 'id')->toArray() : null
            )->load('address_r_id', url('place/region'));
            $form->select('address_r_id', '区县')->placeholder('选择区县')->options(
                $place->address_c_id ? ['-1'=>'选择区县'] + Area::where(['level_type'=>3, 'parent_id'=>$place->address_c_id])->get()->pluck('name', 'id')->toArray() : null
            );
            $form->text('address', '地址')->append('<i class="glyphicon glyphicon-search" style="cursor:pointer" id="address-search"></i>')->rules('required');
            $form->mapPolygon('polygon', '围栏')->options([
                'province'=>'select[name=address_p_id]',
                'city'=>'select[name=address_c_id]',
                'region'=>'select[name=address_r_id]',
                'address'=>'input[name=address]',
                'address_search'=>'i[id=address-search]'
            ]);
        });
    }

}
