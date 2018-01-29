<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{

    //use InsertOnDuplicateKey;
    protected $table='route';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'company_id',
        'title',
        'description',
        'status',
        'created_at',
        'start_date',
        'end_date',
        'order_count',
        'number',
        'updated_at',
        'send_company',
        'send_contact_name',
        'send_contact_mobile',
        'recevie_company',
        'recevie_contact_name',
        'recevie_contact_mobile',
        'guess_cost',
        'real_cost',
        'guess_kilometer',
        'guess_fuel',
        'guess_weight',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function goods() {
        return $this->hasMany(RouteGoods::class, 'route_id');
    }

    public function place() {
        return $this->hasMany(RoutePlace::class, 'route_id')->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    public function partner() {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }

    //常用司机
    public function driver() {
        return $this->hasMany(RouteDriver::class, 'route_id');
    }

    //常用车辆
    public function auto() {
        return $this->hasMany(RouteAuto::class, 'route_id');
    }

    public function plan() {
        return $this->hasMany(RoutePlan::class, 'route_id');
    }
}
