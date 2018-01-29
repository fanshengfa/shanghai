<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Overdraft extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='overdraft';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'unit',
        'company_id',
        'description',
        'create_time',
        'is_advance',
        'status',
        'update_time',
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
