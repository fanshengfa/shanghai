<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class RouteAuto extends Model
{
    protected $table='route_auto';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route_id',
        'company_id',
        'auto_id'
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function auto() {
        return $this->hasOne(Auto::class, 'id', 'auto_id');
    }
}
