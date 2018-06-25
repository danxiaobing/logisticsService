<?php
/**
 * Created by PhpStorm.
 * User: zhangbingxin
 * Date: 2018/6/22
 * Time: 15:25
 */
class App_Carrier_DispatchdetailController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 运输详情
     * @param $dispatch_id
     * @return mixed
     */
    public function getCarrierFunc($dispatch_id){
        $carrier = new App_Carrier_DispatchdetailModel(Yaf_Registry::get("db"));
        $data = $carrier->getCarrier($dispatch_id);
        return $data;
    }



}