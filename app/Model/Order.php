<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const TASK_START = 1;
    const DRIVER_ACCEPT = 9;
    public static $statusDict = [
        '-5' => '未接单',
        '-4' => '任务意外终止',
        '-3' => '核销完成',
        '-2' => '已提交核销',
        '-1' => '已回到车场',
        '0'  => '待司机接单',
        '1'  => '任务开始',
        '2'  => '任务完成',
        '3'  => '返程',
        '4'  => '休息',
        '5'  => '加油',
        '6'  => '维修',
        '7'  => '堵车',
        '8'  => '发生事故',
        '9'  => '司机接单',
        '10' => '车辆检查',
        '20' => '待审核',
        //'21' => '审核通过',审核通过后直接把运单状态设置为0
        '22' => '审核拒绝',
    ];
    public static $stateMachine = [
        '0'=>['-5','9'],
        '9'=>['1', '-4'],
        '1'=>['3', '4', '5', '6', '7', '8', '10'],
        '3'=>['-1'],
        '-1'=>['2'],
        '2'=>['-2'],
        '-2'=>['-3'],
        '4'=>['4', '5', '6', '7', '8', '10', 'resume'],
        '5'=>['4', '5', '6', '7', '8', '10', 'resume'],
        '6'=>['4', '5', '6', '7', '8', '10', 'resume'],
        '7'=>['4', '5', '6', '7', '8', '10', 'resume'],
        '8'=>['4', '5', '6', '7', '8', '10', 'resume'],
        '10'=>['4', '5', '6', '7', '8', '10', 'resume'],
    ];

    public static $pauseStatus = [
        '4', '5', '6', '7', '8', '10'
    ];
    
    /**
     * 获取执行中的状态
     */
    public static $executingStatus = ['-1', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    /**
     * 获取已完成的状态
     */
    public static $historyStatus = ['-5', '-4', '-3', '-2'];

    /**
     * 获取待核销的状态
     */
    public static $checkStatus = ['-2'];


    protected $table='order';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'driver_id',
        'auto_id',
        'partner_id',
        'route_id',
        'description',
        'created_at',
        'guess_start_time',
        'guess_end_time',
        'real_start_time',
        'real_end_time',
        'place_count',
        'status',
        'verifier_id',
        'dispatcher_id',
        'kilometer',
        'cargo_kilometer',
        'guess_kilometer',
        'fuel',
        'guess_fuel',
        'urea',
        'guess_urea',
        'driving_route',
        'updated_at',
        'custom_num',
        'assistant_id',
        'trailer_id',
        'place_id',
        'last_status',
        'number',
        'send_company',
        'send_contact_name',
        'send_contact_mobile',
        'recevie_company',
        'recevie_contact_name',
        'recevie_contact_mobile',
        'weight',
        'guess_weight',
        'check_weight',
        'check_fuel',
        'check_kilometer',
        'check_autoday',
        'guess_cost',
        'check_cost',
        'check_cargo_kilometer',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    protected $casts = [
        'status' => 'string',
        'last_status' => 'json',
    ];

    public function route() {
        return $this->hasOne(Route::class, 'id', 'route_id');
    }

    public function driver() {
        return $this->hasOne(Driver::class, 'id', 'driver_id');
    }

    public function goods() {
        return $this->hasMany(OrderGoods::class, 'order_id');
    }

    public function place() {
        return $this->hasMany(OrderPlace::class, 'order_id');
    }

    public function overdraft() {
        //return $this->hasMany(OrderOverdraft::class, 'order_id');
        return $this->belongsToMany(Overdraft::class, 'order_overdraft', 'order_id', 'overdraft_id')
            ->withPivot(['value']);
            //->withPivot(['id', 'value', 'status', 'return_value', 'description']);
    }

    public function card() {
        //return $this->hasMany(OrderCard::class, 'order_id');
        $orderCard = new OrderCard();
        return $this->belongsToMany(Card::class, 'order_card', 'order_id', 'card_id')
            ->withPivot($orderCard->getFillable());
    }

    public function partner() {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }

    public function auto() {
        return $this->hasOne(Auto::class, 'id', 'auto_id');
    }

    public function dispatcher() {
        return $this->hasOne(AdminUser::class, 'id', 'dispatcher_id');
    }


    public function setLastStatusAttribute($status) {
        $last_status = $this->last_status;
        $last_status = empty($last_status) || !is_array($last_status) ? [] : $last_status;
        array_push($last_status, $status);
        $this->attributes['last_status'] = \json_encode($last_status);
    }

    public function setStatusAttribute($status) {
        $this->attributes['status'] = strval($status);
    }
}


