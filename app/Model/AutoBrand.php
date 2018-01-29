<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class AutoBrand extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='auto_brand';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'logo',
        'alpha',
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
