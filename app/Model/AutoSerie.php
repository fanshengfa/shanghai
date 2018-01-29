<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class AutoSerie extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='auto_serie';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auto_brand_id',
        'name',
        'img',
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
