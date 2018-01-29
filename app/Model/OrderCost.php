<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderCost extends Model
{
    protected $table='order_cost';
    protected $primaryKey='id';

    public static $otherConf = [
        'fuel'=>['name'=>'加油量','is_required'=>0, 'unit'=>'升'],
        'weight'=>['name'=>'重量','is_required'=>0,'unit'=>'吨'],
        'fee'=>['name'=>'费用','is_required'=>1,'unit'=>'元']
    ];

    public static $conf =
        [
            [
                'fee_type'=>1,
                'fee_name'=>'燃油费',
                'is_auto'=>0,
                'fee_input_items'=>[
                    ['key'=>'remark','name'=>'捎句话','is_required'=>0],
                    ['key'=>'fuel','name'=>'加油量','is_required'=>0, 'unit'=>'升'],
                    ['key'=>'fee','name'=>'费用','is_required'=>1, 'unit'=>'元']
                ],
            ],
            [
                'fee_type'=>2,
                'fee_name'=>'桥路费',
                'is_auto'=>0,
                'fee_input_items'=>[
                    ['key'=>'remark','name'=>'捎句话','is_required'=>0],
                    ['key'=>'weight','name'=>'重量','is_required'=>0,'unit'=>'吨'],
                    ['key'=>'fee','name'=>'费用','is_required'=>1,'unit'=>'元']
                ],
            ],
            [
                'fee_type'=>3,
                'fee_name'=>'罚款',
                'is_auto'=>0,
                'fee_input_items'=>[
                    ['key'=>'remark','name'=>'捎句话','is_required'=>0],
                    ['key'=>'fee','name'=>'费用','is_required'=>1,'unit'=>'元']
                ],
            ],
            [
                'fee_type'=>4,
                'fee_name'=>'维修费',
                'is_auto'=>0,
                'fee_input_items'=>[
                    ['key'=>'remark','name'=>'捎句话','is_required'=>0],
                    ['key'=>'fee','name'=>'费用','is_required'=>1,'unit'=>'元']
                ],
            ],
            [
                'fee_type'=>5,
                'fee_name'=>'自定义',
                'is_auto'=>1,
                'fee_input_items'=>[
                    ['key'=>'fee_name','name'=>'自定义费用名称','is_required'=>1],
                    ['key'=>'remark','name'=>'捎句话','is_required'=>0],
                    ['key'=>'fee','name'=>'费用','is_required'=>1,'unit'=>'元']
                ],
            ],
        ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'driver_id',
        'fee',
        'fee_name',
        'fee_type',
        'other',
        'lat',
        'lng',
        'remark',
        'photo',
        'pay_type',
        'created_at',
    ];

    public $timestamps=false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
}


