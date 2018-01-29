<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{

    //use InsertOnDuplicateKey;
    protected $table='order_goods';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'gname',
        'uweight',
        'num',
        'tweight',
        'distance',
        'fee',
        'spec_l',
        'spec_w',
        'spec_h'
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
