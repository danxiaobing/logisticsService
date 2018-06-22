<?php

/**
 * 付款单的订单
 */
class Payment_MasterController extends Rpc
{
    public function init()
    {
        parent::init();
    }


    public function getPaymentListFunc($params){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }



    public function getInfoFunc($id){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->getInfo($id);
        return $data;
    }

}