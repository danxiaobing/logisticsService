<?php
/**
 * Created by PhpStorm.
 * User: zhangbingxin
 * Date: 2018/6/22
 * Time: 15:25
 */
class App_Carrier_DriverController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 司机搜索 or 司机列表
     * @param $dispatch_id
     * @return mixed
     */
    public function getSearchFunc($params){
        $carrier = new App_Carrier_DriverModel(Yaf_Registry::get("db"));
        $data = $carrier->getSearch($params);
        return $data;
    }



}