<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Auto extends Model
{
    //use InsertOnDuplicateKey;
    protected $table='auto';
    protected $primaryKey='id';

    public static $statusDict = [
        '-2' => '已删除',
        '-1' => '已报废',
        '0'  => '空闲中',
        '1'  => '工作中',
        '2'  => '已停用',
    ];

    public static $runStatusDict = [
        '0'  => '未知',
        '1'  => '空驶',
        '2'  => '载重',
    ];

    public static $cateDict = ["1"=>"板式货车","2"=>"厢式货车","3"=>"冷藏车"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auto_number',
        'l',
        'w',
        'h',
        'net_component',
        'create_time',
        'production_date',
        'sign_date',
        'status',
        'run_status',
        'lat',
        'lng',
        'loc_report_time',
        'auto_brand_id',
        'auto_serie_id',
        'auto_style_id',
        'auto_cate_id',
        'mileage',
        'company_id',
        'fleet_id',
        'internal_num',
        'current_driver_id',
        'photo',
        'dlink_auto_number',
        'net_rtid',
        'net_status',
        'auto_model',
        'auto_vin',
        'auto_engine_no',
        'auto_issue_date',
        'auto_file_number',
        'auto_approved_passengers_capacity',
        'auto_gross_mass',
        'auto_unladen_mass',
        'auto_approved_load',
        'auto_traction_mass',
        'auto_comment',
        'auto_inspection_record',
        'roadcard_license_no',
        'auto_cate',
        'roadcard_tseat',
        'roadcard_dimensions',
        'roadcard_issue_date',
        'roadcard_expire_date',
        'tci_company',
        'tci_premium',
        'tci_expire_date',
        'tci_tax',
        'vci_company',
        'vci_liability',
        'vci_iop',
        'vci_premium',
        'vci_expire_date',
        'other_photo',
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
    public function brand() {
        return $this->hasOne(AutoBrand::class, 'id', 'auto_brand_id');
    }
    public function kilometer() {
        return $this->hasOne(AutoKilometer::class, 'auto_id', 'id');
    }

    public function company() {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function cty() {
        return $this->hasMany(CtyNet::class, 'serial', 'net_rtid');
    }
}
