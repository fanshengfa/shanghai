<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class RoutePlan extends Model
{

    //use InsertOnDuplicateKey;
    protected $table='route_plan';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route_id',
        'time_type',
        'time_base',
        'guess_start_time',
        'guess_end_time',
        'guess_start_time_type',
        'guess_end_time_type',
        'create_time',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function route() {
        return $this->hasOne(Route::class, 'id', 'route_id');
    }
}
