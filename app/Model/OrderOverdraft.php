<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class OrderOverdraft extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='order_overdraft';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'overdraft_id',
        'value',
        'status',
        'return_value',
        'description',
        'company_id',
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
        return $this->hasOne(Overdraft::class, 'id', 'overdraft_id');
    }
}
