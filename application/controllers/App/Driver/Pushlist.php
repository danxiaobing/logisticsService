<?php
/**
 * Created by PhpStorm.
 * Date: 2018/5/10
 * Time: 15:15
 */
class App_Driver_PushlistController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 获取推送信息列表
     * @param integer $mobile
     * @return array $data
     */
    public function getListFunc($driverid,$page){
        $L = new App_Driver_PushlistModel(Yaf_Registry::get("db"));
        $data = $L->getList($driverid,$page);
        return $data;
    }



}