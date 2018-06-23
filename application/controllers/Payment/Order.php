<?php

/**
 * 付款单的订单
 */
class Payment_OrderController extends Rpc
{
    public function init()
    {
        parent::init();
    }

    /**
     * @param string $orderNo 订单编号
     * @param bool $isOther 是否获得当前收付款单下的所有订单和附件文件
     * @return string
     * 获得订单的所有信息
     */
    public function getListFunc($params){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }


    public function getInfoFunc($id){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        $data = $L->getInfo($id);
        return $data;
    }


    //创建结算单
    public function createpayFunc($params){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        return $L->addPaymentOrder($params);
    }


    //list结算单
    public function getpaylistFunc($params){
           $L =   new Payment_OrderModel(Yaf_Registry::get("db"));
           return $L->getpaylist($params);
    }

    //获取单个信息
    public  function getpayinfoFunc($payid){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        return $L->getpayinfo($payid);   
    }


    public function updatepayFunc($params){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        return $L->updatepay($params);         
    }


    //获取结算单byorderid
    public function infoByorderidFunc($orderid){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        return $L->infoByorderid($orderid);          
    }


}