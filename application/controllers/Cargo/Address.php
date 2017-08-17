<?php

/**
 * 货主用户中心-
 * Class Address
 *  @author  Daley
 * @date    2017-8-4
 * @version $Id$
 */
class Cargo_AddressController extends Rpc {

    public function init() {
        parent::init();
    }

    /**
     * 地址列表
     * @return 数组
     * @author Tina
     */
    public function getListFunc($params,$uid)
    {

        $L = new Cargo_AddressModel(Yaf_Registry::get("db"));
        $data = $L->getCargoAddreslist($params,$uid);
        return $data;
    }
    /**
     * 新增
     */
    public function addFunc($data)
    {
        //print_r($data);die;
        $S = new Cargo_AddressModel(Yaf_Registry:: get("db"));
        $list = $S->addCargoAddress($data);
        return $list;
    }

    /**
     * 细节
     */
    public function getInfoFunc($id = 0)
    {
        $S = new Cargo_AddressModel(Yaf_Registry:: get("db"));
        $data = $S->getCargoAddressInfo($id);
        return $data;
    }

    /**
     * 更新
     */
    public function updateFunc($id = 0, $data = array())
    {
        $L = new Cargo_AddressModel(Yaf_Registry::get("db"));
        $data = $L->updataCargoAddress($data,$id);
        return $data;
    }

    /**
     * 删除
     */
    public function delFunc($id = 0)
    {
        $L = new Cargo_AddressModel(Yaf_Registry::get("db"));
        $data = $L->deleteCargoAddress($id);
        return $data;
    }




}