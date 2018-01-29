<?php

namespace App\Util;
class NetComponent
{

    private $host = 'http://dsp.cumminsgps.cn/';
    private $appid = 'stodsp';
    private $token = '2mzeSVrR2Xg9EmD/';
    private $key = '2mzeSVrR2Xg9EmD/';
    private $iv = '2mzeSVrR2Xg9EmD/';

    /*
    static $host = 'http://dsp.cumminsgps.cn/';
    static $appid = 'stodsp';
    static $token = '2mzeSVrR2Xg9EmD/';
    static $key = 'HmacSHA256';
    static $timestamp;
    static $format = "json";
    static $infoURL = "dsp/gps/get_info";
    static $installURL = "dsp/gps/install";
    static $removeURL = "dsp/gps/remove";
    */

    public static $timestamp;
    public $code;
    public $error;

    public static $serKeyArr = [
        "time",
        "serial",
        "lng",
        "lat",
        "dire",
        "speed",
        "ECMspeed",
        "ACCandENG" => [
            "KEY",
            "ENG"
        ],
        "Alarm" => [
            "harnessState",
            "LEVState",
            "CANState"
        ],
        "totalFuelUsed",
        "ecmTotalVehicleDistance",
        "ecmTotalVehicleDistance2",
        "ecmTotalVehicleDistance3",
        "meterTotalVehicleDistance",
        "totalHours",
        "totalIdleFuelUsed",
        "totalIdleHours",
        "gatingSwitchState"
    ];

    public static $closest = [];
    /*错误码说明
    115 DSP库终端设备都已安装且匹配
    */

    const CTY = 'cty';
    const GPS = 'gps';

    public static $PathType = [
        self::CTY => 'cty',
        self::GPS => 'cgps',
    ];

    function __construct($options = array())
    {

    }

    /**
     * 对请求数据签名
     */
    private function sign($data)
    {
        $signStr = '';
        foreach ($data as $key => $val) {
            if ($key == 'sig' || $val === '') {
                continue;
            }
            $signStr .= $val;
        }
        //$hashedKey = hash('sha256', $this->token);
        $hashedKey = $this->token;
        $hash = hash_hmac('sha256', $signStr, $hashedKey, true);
        return md5($hash);
    }

    private function request($url, $data, $method)
    {
        $phpver = phpversion();
        $data['appid'] = $this->appid;
        $data['format'] = 'json';
        $data['timeStamp'] = date('Y-m-d H:i:s');
        $data['sig'] = $this->sign($data);
        if ($method == 'get') {
            $url .= '?' . http_build_query($data);
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        if ($method == 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        if ( version_compare($phpver, '5.5', '<=') ) {
            curl_setopt($curl, CURLOPT_CLOSEPOLICY,CURLCLOSEPOLICY_LEAST_RECENTLY_USED);
        } elseif(version_compare($phpver, '7.0', '<')) {
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
        }else{//7.0后，curl不支持设置 CURLOPT_SAFE_UPLOAD设置为false，只能是true。
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        }
        $res = curl_exec($curl);
        curl_close($curl);
        if ($res === false) {
            $this->code = curl_errno($curl);
            $this->error = curl_error($curl);
            return FALSE;
        }
        $data = json_decode($res, true);
        if (empty($data)) {
            $this->code = 'JsonParserError';
            $this->error = 'json解析出错';
            return FALSE;
        }
        if ($data['ret'] == '115') {
            return $data['data'];
        }
        if ($data['ret'] != 0) {
            $this->code = 'OptionError';
            $this->error = $data['data'][0]['msg'];
            return FALSE;
        }
        return $data['data'];
    }

    /**
     * 安装设备
     * @param string $net_rtid 设备编号
     * @param string $auto_number 车牌号码
     */
    public function install($net_rtid, $auto_number)
    {
        //$net_rtid = '3201528';
        //$auto_number = '京AS0863';
        $url = $this->host . "/dsp/gps/install";
        $data['rtID'] = $net_rtid;
        $data['vehicleName'] = $auto_number;
        $data['installTime'] = date('Y-m-d H:i:s');
        $data["sin"]         = $this->sign($data);
        return $this->request($url, $data, 'get');
    }

    /**
     * 根据车牌号获取编号信息
     * @param type $vehicleName
     * @return type
     */
    public function getInfoByAutoNum($vehicleName){
        $url = $this->host . "/dsp/gps/get_info";
        //设置当前时间
        $this->timestamp = date("Y-m-d H:i:s");
        //签名内容
        $data = [
            "format" => 'json',//数据返回格式
            "timeStamp" => $this->timestamp,//当前时间
            "vehicleName" => $vehicleName//车牌号
        ];
        $data["sin"] = $this->sign($data);

        $response = $this->request($url, $data);

        return $response;

        //签名内容
        $data = [
            "vehicleName" => $vehicleName//车牌号
        ];
        $data["sin"] = $this->sign($data);

        $response = $this->request($url, $data);

        return $response;
    }

    /**
     * 移除设备
     * @param string $net_rtid 设备编号
     */
    public function remove($net_rtid)
    {

    }

    /**
     * AES数据解密
     * @param string $data 加密字符串
     * @return string 解密后字符串
     */
    public function decrypt($data)
    {
        return rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_128,
                $this->key,
                $data,
                MCRYPT_MODE_CBC,
                $this->iv),
            "\0");
    }


    /**
     * 序列化
     * @param array $ctyDataRow
     * @return string
     */
    public function serialize($ctyDataRow)
    {
        $ret = "";
        foreach (self::$serKeyArr as $key => $val) {
            if ($val == 'lng' || $val == 'lat') {
                $ctyDataRow[$val] = sprintf("%0.6f", $ctyDataRow[$val]);
            }
            if (is_array($val)) {
                foreach ($val as $subKey) {
                    $ret .= "{$ctyDataRow[$key][$subKey]}^";
                }
            } else {
                $ret .= "{$ctyDataRow[$val]}^";
            }
        }
        $ret = trim($ret, '^') . PHP_EOL;
        return $ret;
    }

    /**
     * 反序列化
     * @param string $str
     * @return array
     */
    public function unserialize($str)
    {
        $index = 0;
        $ret = [];
        $arr = explode('^', $str);
        foreach (self::$serKeyArr as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $subKey) {
                    $ret[$key][$subKey] = $arr[$index];
                    $index++;
                }
            } else {
                $ret[$val] = $arr[$index];
                $index++;
            }
        }
        return $ret;
    }

    /**
     * 获取指定日期段内每一天的日期
     * @param  timestamp $startdate 开始日期
     * @param  timestamp $enddate 结束日期
     * @return Array
     */
    public function getDateFromRange($stimestamp, $etimestamp)
    {
        //计算日期段内有多少天
        $days = ($etimestamp - $stimestamp) / 86400 + 1;
        //保存每天日期
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
        }
        return $date;
    }

    /**
     * 得到文件路径
     * @param string $serial
     * @param date $date
     * @return string
     */
    public function getPath($serial, $date, $PathType = self::CTY)
    {
        $path = WWW_ROOT . '/upload/' . self::$PathType[$PathType];
        $month = date('Y-m', strtotime($date));
        $day = date('d', strtotime($date));
        $fpath = "{$path}/{$serial}/{$month}/{$day}.dat";
        return $fpath;
    }

    /**
     * 获取最近接开始时间 和 结束时间的一组数据
     * @param  string $serial 设备号
     * @param  timestamp $startdate 开始日期
     * @param  timestamp $enddate 结束日期
     * @return float
     */
    private static function getClosest($serial, $stimestamp, $etimestamp = NULL, $all = FALSE)
    {
        $time = time();
        $stimestamp = empty($stimestamp) ? self::$timestamp : $stimestamp;
        $etimestamp = empty($etimestamp) ? self::$timestamp : $etimestamp;
        if (empty($serial) || $etimestamp <= $stimestamp) {
            return FALSE;
        }

        if (empty(self::$closest[$serial])) {
            self::$closest[$serial] = [];
        }

        $stime = date('Y-m-d H:i:s', $stimestamp);
        $etime = date('Y-m-d H:i:s', $etimestamp);
        $dateArr = self::getDateFromRange($stimestamp, $etimestamp);
        $dateCount = count($dateArr);

        if (empty(self::$closest[$serial][$stimestamp])) {
            for ($i = 0; $i < $dateCount; $i++) {
                $fpath = self::getPath($serial, $dateArr[$i]);
                if (!file_exists($fpath)) {
                    continue;
                }
                $fp = new \SplFileObject($fpath, 'r');
                while (!$fp->eof()) {
                    $buf = $fp->current();
                    $fp->next();
                    if (empty($buf)) {
                        continue;
                    }
                    $buf = self::unserialize($buf);
                    if (empty($buf['totalFuelUsed'])) {
                        continue;
                    }
                    self::$closest[$serial][$stimestamp] = $buf;
                    if ($buf['time'] >= $stime) {
                        break;
                    }
                }
                fclose($fp);
                $fp = null;
                break;
            }
            if (self::$closest[$serial][$stimestamp]['time'] < $stime) {
                unset(self::$closest[$serial][$stimestamp]);
            }
        }

        if (empty(self::$closest[$serial][$etimestamp])) {
            for ($i = $dateCount - 1; $i >= 0; $i--) {
                $fpath = self::getPath($serial, $dateArr[$i]);
                if (!file_exists($fpath)) {
                    continue;
                }

                $fp = new \SplFileObject($fpath, 'r');
                while (!$fp->eof()) {
                    $buf = $fp->current();
                    $fp->next();
                    if (empty($buf)) {
                        continue;
                    }
                    $buf = self::unserialize($buf);
                    if (empty($buf['totalFuelUsed'])) {
                        continue;
                    }
                    self::$closest[$serial][$etimestamp] = $buf;
                    if ($buf['time'] >= $etime) {
                        break;
                    }
                }
                fclose($fp);
                $fp = null;
                break;
            }
        }

        return [
            self::$closest[$serial][$stimestamp],
            self::$closest[$serial][$etimestamp]
        ];
    }

    /**
     * 计算里程
     * @param  string $serial 设备号
     * @param  timestamp $startdate 开始日期
     * @param  timestamp $enddate 结束日期
     * @return float
     */
    public static function getKilometer($serial, $stimestamp, $etimestamp = NULL)
    {
        list($sd, $ed) = self::getClosest($serial, $stimestamp, $etimestamp);
        if (empty($sd) || empty($ed)) {
            return 0;
        }
        return sprintf("%0.2f", $ed['ecmTotalVehicleDistance'] - $sd['ecmTotalVehicleDistance']);
    }

    /**
     * 计算油耗
     * @param  string $serial 设备号
     * @param  timestamp $startdate 开始日期
     * @param  timestamp $enddate 结束日期
     * @return float
     */
    public static function getFuel($serial, $stimestamp, $etimestamp = NULL)
    {
        list($sd, $ed) = self::getClosest($serial, $stimestamp, $etimestamp);
        if (empty($sd) || empty($ed)) {
            return 0;
        }
        $ret = sprintf("%0.2f", $ed['totalFuelUsed'] - $sd['totalFuelUsed']);
        return $ret > 0 ? $ret : 0;
    }

    /**
     * 坐标点
     * @param  string $serial 设备号
     * @param  timestamp $startdate 开始日期
     * @param  timestamp $enddate 结束日期
     * @return float
     */
    public static function getPoints($serial, $stimestamp, $etimestamp = NULL, $PathType = self::CTY)
    {
        $lists = [];
        $loc_report_time = '';
        $stimestamp = empty($stimestamp) ? self::$timestamp : $stimestamp;
        $etimestamp = empty($etimestamp) ? self::$timestamp : $etimestamp;
        if (empty($serial) || $etimestamp <= $stimestamp) {
            return [$lists, $loc_report_time];
        }

        $stime = date('Y-m-d H:i:s', $stimestamp);
        $etime = date('Y-m-d H:i:s', $etimestamp);
        $dateArr = self::getDateFromRange($stimestamp, $etimestamp);
        $dateCount = count($dateArr);

        for ($i = 0; $i < $dateCount; $i++) {
            $fpath = self::getPath($serial, $dateArr[$i], $PathType);
            if (!file_exists($fpath)) {
                continue;
            }
            $fp = new \SplFileObject($fpath, 'r');
            while (!$fp->eof()) {
                $buf = $fp->current();
                $fp->next();
                if (empty($buf)) {
                    continue;
                }
                $buf = self::unserialize($buf);
                if ($buf['time'] < $stime || $buf['time'] > $etime) {
                    continue;
                }
                $lists[] = [floatval(sprintf("%0.6f", $buf['lng'])), floatval(sprintf("%0.6f", $buf['lat']))];
                $loc_report_time = $buf['time'];
            }
            fclose($fp);
            $fp = null;
        }
        return [$lists, $loc_report_time];
    }

    /**
     * 存储TY推送过来的数据
     * @param array $data
     */
    public function SaveData($ctyData)
    {
        $path = WWW_ROOT . '/upload/cty';
        !is_dir($path) && @mkdir($path, 0755, true);
        $serialMonthDayFiles = [];
        $totalFuelUsed = 0;
        $ecmTotalVehicleDistance = 0;
        foreach ($ctyData as $row) {
            //如果油耗或者里程数据为空则忽略
            if (empty($row['totalFuelUsed']) || empty($row['ecmTotalVehicleDistance'])) {
                //continue;
            }
            //如果油耗或者里程数据为空则赋值为最近一次不为空的数据
            /*
            $totalFuelUsed = empty($row['totalFuelUsed']) ? $totalFuelUsed : $row['totalFuelUsed'];
            $ecmTotalVehicleDistance = empty($row['ecmTotalVehicleDistance']) ? $ecmTotalVehicleDistance : $row['ecmTotalVehicleDistance'];
            $row['totalFuelUsed'] = empty($row['totalFuelUsed']) ? $totalFuelUsed : $row['totalFuelUsed'];
            $row['ecmTotalVehicleDistance'] = empty($row['ecmTotalVehicleDistance']) ? $ecmTotalVehicleDistance : $row['ecmTotalVehicleDistance'];
            */

            $serial = $row['serial'];
            $month = date('Y-m', strtotime($row['time']));
            $day = date('d', strtotime($row['time']));
            if (!isset($serialMonthDayFiles[$serial])) {
                $tmpPath = "{$path}/{$serial}";
                !is_dir($tmpPath) && @mkdir($tmpPath, 0755, true);
                $serialMonthDayFiles[$serial] = [];
            }
            if (!isset($serialMonthDayFiles[$serial][$month])) {
                $tmpPath = "{$path}/{$serial}/{$month}";
                !is_dir($tmpPath) && @mkdir($tmpPath, 0755, true);
                $serialMonthDayFiles[$serial][$month] = [];
            }
            if (!isset($serialMonthDayFiles[$serial][$month][$day])) {
                $tmpPath = "{$path}/{$serial}/{$month}/{$day}";
                $serialMonthDayFiles[$serial][$month][$day] = [
                    'path' => $tmpPath,
                    'data' => '',
                ];
            }
            $serialMonthDayFiles[$serial][$month][$day]['data'] .= $this->serialize($row);
        }

        $optData = [];
        foreach ($serialMonthDayFiles as $sKey => $serial) {
            foreach ($serial as $mKey => $month) {
                foreach ($month as $dKey => $day) {
                    $optData["{$mKey}-{$mKey}-{$dKey}"] = $day;
                }
            }
        }

        ksort($optData);
        foreach ($optData as $opt) {
            $fp = fopen("{$opt['path']}.dat", 'a+');
            if (flock($fp, LOCK_EX)) {
                fwrite($fp, $opt['data'], strlen($opt['data']));
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }


    /**
     * 存储客户端上报的位置数据
     * @param array $data
     */
    public function SaveClientData($ctyData)
    {
        $path = WWW_ROOT . '/upload/cgps';
        !is_dir($path) && @mkdir($path, 0755, true);
        $serialMonthDayFiles = [];
        foreach ($ctyData as $row) {
            $serial = $row['serial'];
            $month = date('Y-m', strtotime($row['time']));
            $day = date('d', strtotime($row['time']));
            if (!isset($serialMonthDayFiles[$serial])) {
                $tmpPath = "{$path}/{$serial}";
                !is_dir($tmpPath) && @mkdir($tmpPath, 0755, true);
                $serialMonthDayFiles[$serial] = [];
            }
            if (!isset($serialMonthDayFiles[$serial][$month])) {
                $tmpPath = "{$path}/{$serial}/{$month}";
                !is_dir($tmpPath) && @mkdir($tmpPath, 0755, true);
                $serialMonthDayFiles[$serial][$month] = [];
            }
            if (!isset($serialMonthDayFiles[$serial][$month][$day])) {
                $tmpPath = "{$path}/{$serial}/{$month}/{$day}";
                $serialMonthDayFiles[$serial][$month][$day] = [
                    'path' => $tmpPath,
                    'data' => '',
                ];
            }
            $serialMonthDayFiles[$serial][$month][$day]['data'] .= $this->serialize($row);
        }

        $optData = [];
        foreach ($serialMonthDayFiles as $sKey => $serial) {
            foreach ($serial as $mKey => $month) {
                foreach ($month as $dKey => $day) {
                    $optData["{$mKey}-{$mKey}-{$dKey}"] = $day;
                }
            }
        }

        ksort($optData);
        foreach ($optData as $opt) {
            $fp = fopen("{$opt['path']}.dat", 'a+');
            if (flock($fp, LOCK_EX)) {
                fwrite($fp, $opt['data'], strlen($opt['data']));
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }
}

NetComponent::$timestamp = time();