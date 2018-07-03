<?php
/**
 * 托运单
 * @author  amor
 * @date    2017-8-24
 */

class Transmanage_OrderController extends Rpc
{

    public function init()
    {
        parent::init();
    }


    /**
     * 搜索
     * @param array $params
     * @author amor
     */
    public function searchOrderFunc($params){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        $data = $L->searchOrder($params);
        return $data;
    }

    /*获取单个托运单详情*/
    public function getOrderInfoFunc($orderid){
        $O = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        $result = $O->getOrderInfo($orderid);
        return $result;
    }

    public function  untreadOrderFunc($params){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        $data = $L->untreadOrder($params);
        return $data;
    }


    /*获取对应的物流详情*/
    public function getlistFunc($dispatchid){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->getlist($dispatchid);    
    }


    /*获取托运单的生成时间*/
    public function getTimeFunc($orderid){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->getTime($orderid);           
    }

    /*智运后台获取托运单数据*/
    public function getOrderListFunc($serach){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->getOrderList($serach);           
    }

    public function payOrderFunc($id,$param){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->payOrder($id,$param);
    }

    public function updateOrderFunc($id,$param){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->updateOrder($id,$param);
    }

    public function retreatOrderFunc($id,$param){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->retreatOrder($id,$param);
    }


    public  function createpayFunc($params){
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"),null,Yaf_Registry::get("gy_db"));
        return $L->createpay($params);        
    }

}