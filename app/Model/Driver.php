<?php

namespace App\Model;

use App\Model\User;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table='driver';
    protected $primaryKey='id';

    public static $statusDict = [
        '-1' => '已删除',
        '0'  => '空闲中',
        '1'  => '工作中',
        '2'  => '休假中',
    ];

    public static $idnationDict = [
        '汉族'=>'汉族',
        '壮族'=>'壮族',
        '满族'=>'满族',
        '回族'=>'回族',
        '苗族'=>'苗族',
        '维吾尔族'=>'维吾尔族',
        '土家族'=>'土家族',
        '彝族'=>'彝族',
        '蒙古族'=>'蒙古族',
        '藏族'=>'藏族',
        '布依族'=>'布依族',
        '侗族'=>'侗族',
        '瑶族'=>'瑶族',
        '朝鲜族'=>'朝鲜族',
        '白族'=>'白族',
        '哈尼族'=>'哈尼族',
        '哈萨克族'=>'哈萨克族',
        '黎族'=>'黎族',
        '傣族'=>'傣族',
        '畲族'=>'畲族',
        '傈僳族'=>'傈僳族',
        '仡佬族'=>'仡佬族',
        '东乡族'=>'东乡族',
        '高山族'=>'高山族',
        '拉祜族'=>'拉祜族',
        '水族'=>'水族',
        '佤族'=>'佤族',
        '纳西族'=>'纳西族',
        '羌族'=>'羌族',
        '土族'=>'土族',
        '仫佬族'=>'仫佬族',
        '锡伯族'=>'锡伯族',
        '柯尔克孜族'=>'柯尔克孜族',
        '达斡尔族'=>'达斡尔族',
        '景颇族'=>'景颇族',
        '毛南族'=>'毛南族',
        '撒拉族'=>'撒拉族',
        '布朗族'=>'布朗族',
        '塔吉克族'=>'塔吉克族',
        '阿昌族'=>'阿昌族',
        '普米族'=>'普米族',
        '鄂温克族'=>'鄂温克族',
        '怒族'=>'怒族',
        '京族'=>'京族',
        '基诺族'=>'基诺族',
        '德昂族'=>'德昂族',
        '保安族'=>'保安族',
        '俄罗斯族'=>'俄罗斯族',
        '裕固族'=>'裕固族',
        '乌兹别克族'=>'乌兹别克族',
        '门巴族'=>'门巴族',
        '鄂伦春族'=>'鄂伦春族',
        '独龙族'=>'独龙族',
        '塔塔尔族'=>'塔塔尔族',
        '赫哲族'=>'赫哲族',
        '珞巴族'=>'珞巴族',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'internal_num',
        'mobile',
        'email',
        'name',
        'pwd',
        'gender',
        'idnumber',
        'b_y',
        'b_m',
        'b_d',
        'status',
        'created_at',
        'company_id',
        'token',
        'fleet_id',
        'licence_register_date',
        'licence_type',
        'licence_expired_date',
        'licence_num',
        'licence_photo_front',
        'licence_photo_back',
        'idnumber_photo_front',
        'idnumber_photo_back',
        'certificate_photo_front',
        'certificate_photo_back',
        'pix',
        'description',
        'auto_id',
        'current_auto_id',
        'push_channel_id',
        'deviceid',
        'clientid',
        'updated_at',
        'idnation',
        'idaddress',
        'idexpire',
        'other_photo',
        'licence_firstday',
        'licence_archives_no',
        'licence_desc',
        'certificate_firstday',
        'certificate_expired_date',
        'certificate_department',
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
        'other_photo' => 'json',
    ];

    public function auto() {
        return $this->hasOne(Auto::class, 'id','current_auto_id');
    }

    public function fleet() {
        return $this->hasOne(Fleet::class, 'id', 'fleet_id');
    }

    public function company() {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }
}


