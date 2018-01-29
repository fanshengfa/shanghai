<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class RouteDriver extends Model
{
    protected $table='route_driver';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route_id',
        'company_id',
        'driver_id'
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function driver() {
        return $this->hasOne(Driver::class, 'id', 'driver_id');
    }
}
