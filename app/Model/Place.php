<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='place';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'company_id',
        'address_p_id',
        'address_c_id',
        'address_r_id',
        'address',
        'polygon',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    protected $casts = [
        'polygon' => 'json',
    ];

    public function province() {
        return $this->hasOne(Area::class, 'id', 'address_p_id');
    }
    public function city() {
        return $this->hasOne(Area::class, 'id', 'address_c_id');
    }
    public function region() {
        return $this->hasOne(Area::class, 'id', 'address_r_id');
    }
}
