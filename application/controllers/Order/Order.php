<?php

/**
 * 托运单管理
 * Class order
 * @author  Daley
 * @date    2017-8-25
 * @version $Id$
 */
class Order_OrderController extends Rpc {

    public function init() {
        parent::init();
    }



    /**
     * 托运单列表
     * @param array $paramsa
     * @author amor
     */
    public function getOrderListFunc($params){
        $L = new Order_OrderModel(Yaf_Registry::get("db"));
        $data = $L->getOrderList($params);
        return $data;
    }

    /**
     * 新增
     */
    public function addInfoFunc($params)
    {
        $S = new Order_OrderModel(Yaf_Registry:: get("db"));
        $data = $S->addInfo($params);
        return $data;
    }
    /**
     * 细节
     */
    public function getInfoFunc($id)
    {
        $S = new Order_OrderModel(Yaf_Registry:: get("db"));
        $data = $S->getInfo($id);
        return $data;
    }
    /**
     * 细节
     */
    public function getOrderInfoFunc($id)
    {
        $S = new Order_OrderModel(Yaf_Registry:: get("db"));
        $data = $S->getOrderInfo($id);
        return $data;
    }

    /**
     * 更新
     */
    public function updateFunc($id,$data = array())
    {
        $L = new Order_OrderModel(Yaf_Registry::get("db"));
        $data = $L->updata($id,$data);
        return $data;
    }
    /**
     * 货主查找车源-生成托运单
     */
    public function addPublishAndCreateOrderFunc($params)
    {
        $L = new Order_OrderModel(Yaf_Registry::get("db"));
        $data = $L->addPublishAndCreateOrder($params);
        return $data;
    }




}