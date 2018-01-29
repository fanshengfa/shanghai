<?php

namespace App\Model;

use App\Model\Company;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    protected $table='admin_users';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'avatar',
        'remember_token',
        'company_id'
    ];

    public $timestamps=false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function company() {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    protected $with = ['company'];
}


