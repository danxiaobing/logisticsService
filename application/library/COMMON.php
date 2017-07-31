<?php
/**
 * 公共方法类
 *
 * @author  James
 * @date    2011-06-15 15:00
 * @version $Id$
 */

class COMMON
{

    public function __construct()
    {
        //construct class
    }

    /**
     * return result data
     *
     * @param   int         $code  200 ok  300 failed 301 timeout
     * @param   string      $msg
     * @return  string
     */
    public static function ApiJson($code, $msg='', $data = [])
    {
        $res = array(
            'code'   => $code, //"200"
            'message' => $msg,
        ) ;

        if(is_array($data) && count($data) > 0){
            $res['data'] = $data;
        }else{
            $res['data'] = (object)$data;
        }
        echo json_encode($res); exit();
    }

    /**
     * 随机获得编码id
     */
    public static function getCodeId($prefix='') {
        list($min,$sec) = explode(" ",microtime());
        // $min = substr($min,2,6);
        // $id = $prefix.$sec.$min.mt_rand(100,999);
        $min = substr($min,2,2);
        $id = $prefix.date("Ymds").$min;
        return $id;
    }

    public static function dateInitArray($from, $to,$params)
    {
        if(!self::checkDateIsValid($from) || !self::checkDateIsValid($to))
            return [];

        $dest = array();
        foreach ($params as $param)
            $dest[$param] = 0;


        $arr = array();
        $start = strtotime($from);

        $stop = strtotime($to);

        if($start <= $stop){
            for($i =  $start;$i <= $stop;$i += 86400){
                $date = date('Y-m-d',$i);

                $arr[$date] = $dest;
                $arr[$date]['date'] = $date;
            }
        }else{
            for($i =  $start;$i >= $stop;$i -= 86400){
                $date = date('Y-m-d',$i);

                $arr[$date] = $dest;
                $arr[$date]['date'] = $date;
            }
        }


        return $arr;
    }

    public static function  checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d")) {
        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }
        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }

        return false;
    }

}
