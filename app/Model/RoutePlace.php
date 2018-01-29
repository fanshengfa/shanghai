<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class RoutePlace extends Model
{

    //use InsertOnDuplicateKey;
    protected $table='route_place';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        /*
        'route_id',
        'lat',
        'lng',
        'address_p',
        'address_c',
        'address_r',
        'address',
        'map_address_p',
        'map_address_c',
        'map_address_r',
        'map_address',
        'task_type',
        'guess_start_time',
        'guess_end_time',
        'polygon'
        */
        'route_id',
        'place_id',
        'task_type',
        'order',
        'guess_start_time',
        'guess_end_time',
        'guess_start_time_type',
        'guess_end_time_type',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /*
    protected $casts = [
        'polygon' => 'json',
    ];

    public function getPolygonAttribute() {
        if(isset($this->attributes['polygon'])) {
            return \json_decode($this->attributes['polygon'], true);
        } else {
            $this->attributes['polygon'] = \json_encode([[36,119],[36.01,119.01]]);
            return [[36,119],[36.01,119.01]];
        }
    }

    public function setPolygonAttribute($value)
    {
        $this->attributes['polygon'] = \json_encode($value);
    }
    */

    public function place() {
        return $this->hasOne(Place::class, 'id', 'place_id');
    }
}
