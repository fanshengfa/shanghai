<?php

namespace App\Console\Commands;


use App\Model\Area;
use App\Util\NetComponent;
use App\Model\OrderGoods;
use App\Model\OrderPlace;
use App\Model\RouteAuto;
use App\Model\RouteDriver;
use App\Model\RouteGoods;
use App\Model\RoutePlace;
use App\Model\RoutePlan;
use App\Model\Card;
use App\Model\Driver;
use App\Model\Overdraft;
use App\Model\Company;
use App\Model\Auto;
use App\Model\Place;
use App\Model\CtyNet;

use DB;
use App\Model\Route;
use App\Model\Order;
use App\Model\AdminUser;
use Encore\Admin\Facades\Admin;
use Illuminate\Console\Command;
use Encore\Admin\Auth\Database\Role;
use Excel;
use Log;


class DataProc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataproc {--method=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据处理';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $method = $this->option('method');
        $this->$method();
    }

    public function dlink()
    {
        $net_rtid = '1421460';
        $auto_number = '浙A2Q325';
        $netComponent = new NetComponent();
        $install = $netComponent->install($net_rtid, $auto_number);

        var_dump($netComponent->code);

        var_dump($netComponent->error);
        var_dump($install);
    }

    public function auto() {
        $auto_number = '浙A2Q325';
        $netComponent = new NetComponent();
        $install = $netComponent->getInfoByAutoNum($auto_number);
        var_dump($netComponent->code);

        var_dump($netComponent->error);
        var_dump($install);
    }


    public function importplace() {
        /*
         * A序号
         * B中转部
         * C省区
         * D城市
         * E区县
         * F中转部地址
         * G片区
         * H负责人（经理）
         * I联系电话
         * J备注
         */
        $filename = storage_path()."/data/中转站清单.xlsx";
        Excel::load($filename, function($reader) {
            $sheet = $reader->getSheet(0);
            $mc = ($sheet->getMergeCells());

            foreach ($mc as $v) {
                list($col, $row) = explode(':', $v);
                $srow = preg_replace('/[A-Za-z]+/i', '', $col);
                $erow = preg_replace('/[A-Za-z]+/i', '', $row);
                $col  = preg_replace('/\d+/i', '', $col);
                //$sheet = $sheet->unmergeCells($v);
                //echo "$col---$srow---$erow\n";
                for($i=$srow;$i<=$erow;$i++) {
                    $sheet->setCellValue($col.$i, $sheet->getCell($col.$srow)->getValue());
                    continue;
                }
            }
            $maxCol = $sheet->getHighestColumn();
            $maxRow = $sheet->getHighestRow();
            for($i=2; $i<=$maxRow; $i++) {
                $row = [];
                $row[0] = $sheet->getCell('A'.$i)->getValue();
                $row[1] = $sheet->getCell('B'.$i)->getValue();
                $row[2] = $sheet->getCell('C'.$i)->getValue();
                $row[3] = $sheet->getCell('D'.$i)->getValue();
                $row[4] = $sheet->getCell('E'.$i)->getValue();
                $row[5] = $sheet->getCell('F'.$i)->getValue();
                $row[6] = $sheet->getCell('G'.$i)->getValue();
                $row[7] = $sheet->getCell('H'.$i)->getValue();
                $row[8] = $sheet->getCell('I'.$i)->getValue();
                $pname = trim($sheet->getCell('C'.$i)->getValue());
                $cname = trim($sheet->getCell('D'.$i)->getValue());
                $aname = trim($sheet->getCell('E'.$i)->getValue());
                $short_pname = preg_replace('/省|市|区|县|镇$/i', '', trim($sheet->getCell('C'.$i)->getValue()));
                $short_cname = preg_replace('/省|市|区|县|镇$/i', '', trim($sheet->getCell('D'.$i)->getValue()));
                $short_aname = preg_replace('/省|市|区|县|镇$/i', '', trim($sheet->getCell('E'.$i)->getValue()));
                //echo $pname,$cname,$aname,"\n";
                $province = Area::where('short_name', $short_pname)->orWhere('name',$pname)->first();
                $city = Area::where(function($query) use($cname, $short_cname) {
                    $query->where('short_name', $short_cname)->orWhere('name', $cname);
                })->where('parent_id', $province->id)->first();
                $area = Area::where(function($query) use($aname, $short_aname) {
                    $query->where('short_name', $short_aname)->orWhere('name', $aname);
                })->where('parent_id', $city->id)->first();
                if(empty($province) || empty($city) || (empty($area)&&!empty($aname))) {
                    echo $pname."==".$cname."==".$aname,"\n";
                    continue;
                }
                $place = [
                    'name' => trim($sheet->getCell('B'.$i)->getValue()),
                    'company_id' => 3,
                    'address_p_id' => $province->id,
                    'address_c_id' => $city->id,
                    'address_a_id' => $area->id,
                    'address' => trim($sheet->getCell('F'.$i)->getValue()),
                    'contact_name' => trim($sheet->getCell('H'.$i)->getValue()),
                    'contact_tel' => trim($sheet->getCell('I'.$i)->getValue())
                ];
                //print_r($place);
                //continue;
                Place::create($place);
                /*
                $company_name = trim($sheet->getCell('G'.$i)->getValue());
                $company = Company::where('name', $company_name)->first();
                if(empty($company)) {
                    $company = new Company();
                    $company->name = $company_name;
                    $company->parent_id = 3;
                    $company->parent_ids = '';
                    $company->level = 1;
                    $company->contact_name = $sheet->getCell('H'.$i)->getValue();
                    $company->contact_tel = $sheet->getCell('I'.$i)->getValue();
                    $company->save();
                    $company->parent_ids = '3,'.$company->id;
                    $company->save();

                }
                $adminUser = new AdminUser();
                $adminUser->username = $sheet->getCell('I'.$i)->getValue();
                $adminUser->password = bcrypt('123456');
                $adminUser->name     = $sheet->getCell('H'.$i)->getValue();
                $adminUser->save();
                Role::create(['role_id'=>2, 'user_id'=>$adminUser->id]);
                print_r($company->toArray());
                print_r($adminUser->toArray());
                */
            }
        }, 'UTF-8');
    }

    /*
    $json  = '{
        "serial": "1422640",
        "time": "2018-01-19 23:54:00",
        "lng": "119.300788",
        "lat": "33.335115",
        "dire": 148,
        "speed": 72,
        "ECMspeed": 74.9,
        "ACCandENG": {
            "KEY": 1,
            "ENG": 1
        },
        "Alarm": {
            "harnessState": 0,
            "LEVState": 0,
            "CANState": 0
        },
        "totalFuelUsed": 10203,
        "ecmTotalVehicleDistance": 60296.38,
        "ecmTotalVehicleDistance2": 60296.4,
        "ecmTotalVehicleDistance3": null,
        "meterTotalVehicleDistance": 60205.77,
        "totalHours": 942.55,
        "totalIdleFuelUsed": 87.5,
        "totalIdleHours": 46.75,
        "gatingSwitchState": 0
    }';
    */
    public function ctydata() {
        ini_set('memory_limit', '512M');
        $logpath = storage_path()."/ctydata/";
        if(!file_exists($logpath)) {
            mkdir($logpath);
        }
        $logpath = $logpath.date('Ymd').".log";
        $path  = '/var/www/html/iService-netcompant/www/files/net/';
        $etime = time();
        $stime = $etime-86400*2;
        $pathArr = [];
        for($stime;$stime<=$etime;$stime+=86400) {
            $pathArr[] = $path.date('Y/m/d/',$stime);
        }
        $fileArr = [];
        $serialArr = [];
        foreach($pathArr as $val) {
            $handle = opendir($val);
            if(empty($handle)) {
                continue;
            }
            while (($file=readdir($handle)) !== false) {
                if($file == '.' || $file == '..' || strpos($file, '.txt')===false) {
                    continue;
                }
                $fileArr[] = $val . DIRECTORY_SEPARATOR . $file;
                $serialArr[str_replace('.txt', '', $file)] = $file;
            }
            closedir($handle);
        }
        $ctyNet = CtyNet::whereIn('serial', array_keys($serialArr))->groupBy('serial')->select('serial',DB::raw('max(`time`) max_time'))->get();
        $ctyNet = $ctyNet ? $ctyNet->pluck('max_time', 'serial')->toArray() : [];
        foreach($fileArr as $file) {
            $serial = preg_replace('/.*\/(\d+)\.txt$/', '$1', $file);
            $time   = '';
            if(isset($ctyNet[$serial]) && $ctyNet[$serial]) {
                $file = $path.date('Y/m/d/', strtotime($ctyNet[$serial])).$serial.'.txt';
                $time = $ctyNet[$serial];
                $ctyNet[$serial] = false;
            } elseif(isset($ctyNet[$serial]) && $ctyNet[$serial]===false) {
                continue;
            }
            $fp = new \SplFileObject($file, 'r');
            while (!$fp->eof()) {
                $buf = trim($fp->current());
                $fp->next();
                if (empty($buf)) {
                    continue;
                }
                $json = json_decode($buf, true);
                if(empty($json)) {
                    continue;
                }
                foreach($json as $net) {
                    if($time && $net['time']<$time) {
                        continue;
                    }
                    $data = [
                        "serial" => $net['serial'],
                        "time" => $net['time'],
                        "lng" => $net['lng'],
                        "lat" => $net['lat'],
                        "dire" => $net['dire'],
                        "speed" => $net['speed'],
                        "ECMspeed" => $net['ECMspeed'],
                        "ACCandENG_KEY" => $net['ACCandENG']['KEY'],
                        "ACCandENG_ENG" => $net['ACCandENG']['ENG'],
                        "Alarm_harnessState" => $net['Alarm']['harnessState'],
                        "Alarm_LEVState" => $net['Alarm']['LEVState'],
                        "Alarm_CANState" => $net['Alarm']['CANState'],
                        "totalFuelUsed" => $net['totalFuelUsed'],
                        "ecmTotalVehicleDistance" => $net['ecmTotalVehicleDistance'],
                        "ecmTotalVehicleDistance2" => $net['ecmTotalVehicleDistance2'],
                        "ecmTotalVehicleDistance3" => $net['ecmTotalVehicleDistance3'],
                        "meterTotalVehicleDistance" => $net['meterTotalVehicleDistance'],
                        "totalHours" => $net['totalHours'],
                        "totalIdleFuelUsed" => $net['totalIdleFuelUsed'],
                        "totalIdleHours" => $net['totalIdleHours'],
                        "gatingSwitchState" => $net['gatingSwitchState'],
                    ];
                    $data = array_filter($data);
                    CtyNet::insertOnDuplicateKey($data);
                    $log = date('Y-m-d H:i:s')."=={$net['time']}=={$net['serial']}==\n";
                    echo $log;
                    file_put_contents($logpath, $log, FILE_APPEND);
                }
            }
            fclose($fp);
        }
    }
}
