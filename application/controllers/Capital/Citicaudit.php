<?php

/**
 *账户申请验证
 * Created by PhpStorm.
 * User: xingjun
 * Date: 2017/3/21
 * Time: 10:29
 */
class Capital_CiticauditController extends Rpc
{
    /**
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        #   Yaf_Dispatcher::getInstance()->disableView();
    }

    public function getListFunc($params)
    {
        $Cc = new Capital_CiticauditModel(Yaf_Registry::get("db"));
        $data = $Cc->getList($params);
        return $data;
    }

    public function getInfoFunc($id)
    {
        $Cc = new Capital_CiticauditModel(Yaf_Registry::get("db"));
        $data = $Cc->getInfoById($id);
        return $data;
    }

    public function saveAccountFunc($data,$id)
    {
        $Cc = new Capital_CiticauditModel(Yaf_Registry::get("db"));
        $data = $Cc->saveAccount($data,$id);
        return $data;
    }

    public function addAccountFunc($data)
    {
        $Cc = new Capital_CiticauditModel(Yaf_Registry::get("db"));
        $data = $Cc->addAccount($data);
        return $data;
    }

    public function saveAccountApplyFunc($data,$id)
    {
        $Cc = new Capital_CiticauditModel(Yaf_Registry::get("db"));
        $data = $Cc->saveAccountApply($data,$id);
        return $data;
    }

    /**
     * 获取ca签章信息
     * @param $data
     * @param $id
     * @return mixed
     */
    public function getCaSealCustomerIdFunc($carrier_id,$cargo_id)
    {
        $Cc = new Capital_CiticauditModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Cc->getCaSealCustomerId($carrier_id,$cargo_id);
        return $data;
    }
}