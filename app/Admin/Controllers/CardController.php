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
use App\Model\Card;
use App\Model\AutoSerie;
use App\Model\AutoStyle;
use App\Model\Fleet;
use App\Model\Order;

class CardController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('卡管理');
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
            $content->header('卡管理');
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    public function edit($id) {
        return Admin::content(function (Content $content) use ($id){
            $content->header('卡管理');
            $content->description(trans('admin.edit'));
            $content->body($this->form($id)->edit($id));
        });
    }

    public function grid() {
        Admin::script('
        window.Recharge = function(id, card_number, card_type) {
          var card_type = card_type==1 ? "燃油卡" : "路桥卡";
          var this_money = prompt("卡号:"+card_number+"\n\n卡类型:"+card_type+"\n\n充值金额:","");
          if (this_money==null || this_money=="") {
            return;
          }
          $.ajax({
              url:"/card/"+id,
              type:"POST",
              dataType:"json",
              data:{this_money:this_money, _token:LA.token, _method:"put"},
              success:function(data){
                if(data.status) {
                    location.reload();
                }
              }
          });
        }
        ');
        return Admin::grid(Card::class, function(Grid $grid) {
            $user = $this->user();
            $grid->model()->where(['company_id'=>$user['company_id']]);
            $grid->column('id', 'ID')->sortable();
            $grid->column('card_type', '类型')->display(function(){
                return array_get(Card::$typeDict, $this->card_type, '未知');
            });
            $grid->column('card_number', '卡号');
            $grid->column('begin_balance', '期初金额（元）');
            $grid->column('this_money', '本次发生金额（元）');
            $grid->column('end_balance', '期末金额（元）');
            $grid->column('balance', '当前结余（元）');
            $grid->column('card_status', '状态')->display(function(){
                return array_get(Card::$statusDict, $this->card_status, '未知');
            });
            $grid->actions(function(Grid\Displayers\Actions $actions){
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="javascript:{Recharge('.$actions->getKey().', \''.$actions->row->card_number.'\', \''.$actions->row->card_type.'\')}"><i class="fa fa fa-money"></i></a>');
            });
            $grid->filter(function (Filter $filter) use($user) {
                $filter->useModal();
                $filter->like('internal_num', '卡号');
                $filter->equal('card_type', '类型')->select(Card::$statusDict);
                $filter->equal('card_status', '状态')->select(Card::$statusDict);
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
        return Admin::form(Card::class, function (Form $form) use($id) {
            $user = $this->user();
            $form->display('id', 'ID');
            $form->select('card_type', '类型')->options(Card::$typeDict);
            $form->text('card_number', '卡号');
            $form->text('begin_balance', '期初金额（元）');
            $form->text('this_money', '本次发生金额（元）');
            $form->text('end_balance', '期末金额（元）');
            $form->text('balance', '当前结余（元）');
            $form->select('card_status', '状态')->options(Card::$statusDict);
            $form->hidden('company_id')->default($user['company_id']);
        });
    }

}
