<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='fleet';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'updated_at',
        'created_at',
        'driver_count',
        'auto_count',
        'company_id',
        'trailer_count',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

}
