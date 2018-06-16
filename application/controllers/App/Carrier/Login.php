<?php
/**
 * Created by PhpStorm.
 * User: weicheng
 * Date: 2018/5/10
 * Time: 15:15
 */
class App_Carrier_LoginController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 获取司机押运员
     * @param integer $mobile
     * @return array $data
     */
    public function getCarrierFunc($username,$userpwd){
        $carrier = new App_Carrier_CarrierModel(Yaf_Registry::get("db"));
        $data = $carrier->getCarrier($username,$userpwd);
        return $data;
    }



}