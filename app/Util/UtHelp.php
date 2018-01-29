<?php

namespace App\Util;

class UtHelp {

    /**
     * 验证身份证号
     * @param $vStr
     * @return bool|array
     */
    public static function idNumberCheck($vStr)
    {
        $vCity = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr))
            return false;

        if (!in_array(substr($vStr, 0, 2), $vCity))
            return false;

        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);

        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);

        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }

        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday)
            return false;

        if ($vLength == 18) {
            $vSum = 0;

            for ($i = 17 ; $i >= 0 ; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }

            if($vSum % 11 != 1)
                return false;
        }

        # 1男, 2女
        $gender = 2;
        if (intval(substr($vStr, 16, 1)) % 2 == 1) {
            $gender = 1;
        }

        return [$vBirthday, $gender];
    }

    public static function radian($d) {
        return $d * 3.1415926535898 / 180.0;
    }

    //根据经纬度计算距离
    public static function distanceCalculate($lng1, $lat1, $lng2, $lat2) {
        $radLat1 = self::radian( $lat1 );
        $radLat2 = self::radian( $lat2 );
        $a = self::radian( $lat1 ) - self::radian( $lat2 );
        $b = self::radian( $lng1 ) - self::radian( $lng2 );

        $s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) *
                cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) );
        $s = $s * 6378137; //乘上地球半径，单位为米
        return round($s); //单位为米
    }

    public static function json_decode($data) {
        return json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }

    public static function output($data, $options, $sheetName) {
        require_once dirname(__FILE__).'/../Libs/PHPExcel.php';
        $Excel = new \PHPExcel();
        $Excel->setActiveSheetIndex(0);
        $Sheet = $Excel->getActiveSheet();
        $Sheet->setTitle($sheetName);
        $Cells = range('A','Z');
        foreach ($options as $k=>$opt) {
            $opt['width'] and $Sheet->getColumnDimension($Cells[$k])->setWidth($opt['width']);
            $Sheet->setCellValue("{$Cells[$k]}1", $opt['name']);
        }
        $row = 2;
        foreach ($data as $d) {
            foreach ($options as $k=>$opt) {
                $content = '';
                if(is_array($opt['column'])) {
                    foreach ($opt['column'] as $col) {
                        $content .= $d[$col]."\n";
                    }
                } else {
                    $content = $d[$opt['column']];
                }
                $Sheet->setCellValueExplicit("{$Cells[$k]}{$row}", $content, \PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $row++;
        }

        $Writer = new \PHPExcel_Writer_Excel5($Excel);
        $outputFileName =  $sheetName.'-'.date('YmdHis').".xls";
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:attachment;filename="' . $outputFileName . '"');  //到文件
        //header('Content-Disposition:inline;filename="'.$outputFileName.'"');  //到浏览器
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $Writer->save('php://output');
        exit();
    }


    /**
     * @description 射线法判断点是否在多边形内部
     * @param {Array} p 待判断的点，格式：{ x: X坐标, y: Y坐标 }
     * @param {Array} poly 多边形顶点，数组成员的格式同 p
     * @return {String} 点 p 和多边形 poly 的几何关系
     */
    public static function rayCasting($p, $poly) {
        $px = $p['x'];
        $py = $p['y'];
        $flag = false;
        $length = count($poly);
        //for($i = 0, $l = $length, $j = $l - 1; $i < $l; $j = $i, $i++) {
        for($i=0,$j=1; $j<$length; $i++, $j++) {
            $sx = $poly[$i]['x'];
            $sy = $poly[$i]['y'];
            $tx = $poly[$j]['x'];
            $ty = $poly[$j]['y'];

            // 点与多边形顶点重合
            if(($sx === $px && $sy === $py) || ($tx === $px && ty === $py)) {
                return 'on';
            }

            // 判断线段两端点是否在射线两侧
            if(($sy < $py && $ty >= $py) || ($sy >= $py && $ty < $py)) {
                // 线段上与射线 Y 坐标相同的点的 X 坐标
                $x = $sx + ($py - $sy) * ($tx - $sx) / ($ty - $sy);

                // 点在多边形的边上
                if($x === $px) {
                    return 'on';
                }

                // 射线穿过多边形的边界
                if($x > $px) {
                    $flag = !$flag;
                }
            }
        }

        // 射线穿过多边形边界的次数为奇数时点在多边形内
        return $flag ? 'in' : 'out';
    }
}