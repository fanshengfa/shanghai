<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Trailer extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='trailer';
    protected $primaryKey='id';

    public static $statusDict = [
        '-2' => '已删除',
        '-1' => '已报废',
        '0'  => '空闲中',
        '1'  => '工作中',
        '2'  => '已停用',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "trailer_number",
        "l",
        "w",
        "h",
        "status",
        "company_id",
        "fleet_id",
        "auto_id",
        "internal_num",
        "photo",
        "trailer_type",
        "trailer_owner",
        "trailer_address",
        "trailer_use_character",
        "trailer_model",
        "trailer_vin",
        "trailer_issue_date",
        "trailer_file_number",
        "trailer_gross_mass",
        "trailer_unladen_mass",
        "trailer_approved_load",
        "trailer_traction_mass",
        "trailer_comment",
        "trailer_inspection_record",
        "created_at",
    ];

    public $timestamps=false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    protected $casts = [
        'photo' => 'json',
        'other_photo' => 'json',
    ];

    public function fleet() {
        return $this->hasOne(Fleet::class, 'id', 'fleet_id');
    }
    public function auto() {
        return $this->hasOne(Auto::class, 'id', 'auto_id');
    }
}
