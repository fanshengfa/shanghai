<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table='partner';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_name',
        'contact_tel',
        'address_p',
        'address_c',
        'address_r',
        'address',
        'create_time',
        'name',
        'company_id',
        'description',
        'contact_email',
        'number',
        'domain_name',
        'logo_url',
        'lat',
        'lng'
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


