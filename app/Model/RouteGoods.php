<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class RouteGoods extends Model
{

    //use InsertOnDuplicateKey;
    protected $table='route_goods';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route_id',
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
