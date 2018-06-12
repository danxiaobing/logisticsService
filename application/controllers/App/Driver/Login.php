<?php
/**
 * Created by PhpStorm.
 * User: weicheng
 * Date: 2018/5/10
 * Time: 15:15
 */
class App_Driver_LoginController extends Rpc
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
    public function getDriverFunc($mobile){
        $driver = new App_Driver_DriverModel(Yaf_Registry::get("db"));
        $data = $driver->getDriver($mobile);
        return $data;
    }



}