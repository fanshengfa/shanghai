<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class AutoKilometer extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='auto_kilometer';
    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auto_id',
        'empty',
        'weight',
        'unknown',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function fleet() {
        return $this->hasOne(Fleet::class, 'id', 'fleet_id');
    }
    public function brand() {
        return $this->hasOne(AutoBrand::class, 'id', 'auto_brand_id');
    }
}