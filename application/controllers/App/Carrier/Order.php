<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 11:38
 */
class App_Carrier_OrderController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    public function getListFunc($params)
    {
        $O = new App_Carrier_OrderModel(Yaf_Registry::get("db"));
        $data = $O->getList($params);
        return $data;
    }

    public function getDetailFunc($order_id)
    {
        $O = new App_Carrier_OrderModel(Yaf_Registry::get("db"));
        $data = $O->getDetail($order_id);
        return $data;
    }
}