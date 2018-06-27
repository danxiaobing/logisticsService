<?php
/**
 * Created by PhpStorm.
 * User: ai
 * Date: 2018/6/22
 * Time: 09:32
 */
class App_Carrier_MapController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 上传位置信息
     * @param integer $data
     * @return array $data
     */
    public function addLocationFunc($data){
        $map = new App_Carrier_MapModel(Yaf_Registry::get("db"));
        $data = $map->addLocationInfogather($data);
        return $data;
    }
    /**
     * 获取位置信息
     * @param integer $data
     * @return array $data
     */
    public function getLocusFunc($parms){

        $map = new App_Carrier_MapModel(Yaf_Registry::get("db"));
        $res = $map->getLocus($parms);   
        if (!$res){
            return [];
        }
        $data = [
            "name"=>$res[0]['driver_name'], //司机姓名
            "dispatch_number"=>$res[0]['dispatch_number'], //调度单号
            "mobile"=>$res[0]['mobile'], //手机
            "off_address"=>$res[0]['off_address'], //发货地址
            "reach_address"=>$res[0]['reach_address'], //卸货地址
            "zoom"=>19,          //默认缩放比
        ];
        foreach ($res as $key => $val){
            $data['locations'][] = [
                "lng"=>$val['lng'],  //纬度
                "lat"=>$val['lat'],  //经度
                "created_at"=>$val['created_at']
            ];
        }      
        
        return $data?$data:[];
    }



}