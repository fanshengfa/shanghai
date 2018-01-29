<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class OrderPlace extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='order_place';
    protected $primaryKey='id';

    const NOT_START = 0;
    const LEAVE_DEST = 1;
    const ARRIVED_DEST = 2;
    const START = 3;
    const FINISH = -1;
    
    public static $statusDict = [
        0=>'任务未开始',
        1=>'前往目的地',
        2=>'到达目的地',
        3=>'完成',
        -1=>'任务已经结束',
    ];
    public static $taskTypeDict = [
        1=>'装货',
        2=>'卸货',
    ];
    public static $stateMachine = [
        '0'=>['1'],
        '1'=>['2'],
        '2'=>['3', '4'],
        '3'=>['5'],
        '4'=>['6'],
        '5'=>['-1'],
        '6'=>['-1'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'route_id',
        'place_id',
        'arrived_time',
        'leave_time',
        'task_type',
        'status',
        'polygon',
        'guess_start_time',
        'guess_end_time',
        'real_start_time',
        'real_end_time',
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
        'photo' => 'json',
    ];


    public function place() {
        return $this->hasOne(Place::class, 'id', 'place_id');
    }
}
