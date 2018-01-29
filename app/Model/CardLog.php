<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class CardLog extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='card_log';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'card_id',
        'order_id',
        'begin_balance',
        'end_balance',
        'in_money',
        'out_money',
        'type',
        'created_at'
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
