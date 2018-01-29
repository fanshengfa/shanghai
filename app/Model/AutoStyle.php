<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class AutoStyle extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='auto_style';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auto_serie_id',
        'name',
        'w',
        'h',
        'l',
        'gate_type_id',
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
