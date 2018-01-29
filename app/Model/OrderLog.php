<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $table='order_log';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'driver_id',
        'auto_id',
        'status',
        'lat',
        'lng',
        'map_address_p',
        'map_address_c',
        'map_address_r',
        'map_address',
        'order_place_id',
        'who_add',
        'body',
        'photo',
        'is_place_log',
        'order_place_status',
        'description',
        'remark',
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

    protected $casts = [
        'photo' => 'json',
    ];
}


