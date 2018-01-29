<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class CtyNet extends Model
{
    use InsertOnDuplicateKey;
    protected $table='cty_net';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "serial",
        "time",
        "lng",
        "lat",
        "dire",
        "speed",
        "ECMspeed",
        "ACCandENG_KEY",
        "ACCandENG_ENG",
        "Alarm_harnessState",
        "Alarm_LEVState",
        "Alarm_CANState",
        "totalFuelUsed",
        "ecmTotalVehicleDistance",
        "ecmTotalVehicleDistance2",
        "ecmTotalVehicleDistance3",
        "meterTotalVehicleDistance",
        "totalHours",
        "totalIdleFuelUsed",
        "totalIdleHours",
        "gatingSwitchState",
    ];

    public $timestamps=false;

    protected $casts = [
        'lng' => 'Float',
        'lat' => 'Float',
    ];
}
