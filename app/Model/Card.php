<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='card';
    protected $primaryKey='id';

    public static $statusDict = [
        '0'  => '不可以预支',
        '1'  => '可以预支',
    ];

    public static $typeDict = [
        '0'  => '燃油卡',
        '1'  => '路桥卡',
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'card_number',
        'card_type',
        'card_status',
        'begin_balance',
        'end_balance',
        'this_money',
        'balance',
        'company_id',
        'updated_at',
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

    public function log() {
        return $this->hasMany(CardLog::class, 'card_id');
    }
}
