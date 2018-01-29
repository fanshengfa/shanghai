<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='area';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'parent_id',
        'short_name',
        'level_type',
        'city_code',
        'zip_code',
        'merger_name',
        'lng',
        'lat',
        'pinyin',
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
