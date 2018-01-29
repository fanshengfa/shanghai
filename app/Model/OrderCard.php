<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class OrderCard extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='order_card';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'card_id',
        'value',
        'status',
        'return_value',
        'description',
        'company_id',
        'use_value',
    ];

    public $timestamps=false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function overdraft() {
        return $this->hasOne(OrderOverdraft::class, 'id', 'overdraft_id');
    }
}
