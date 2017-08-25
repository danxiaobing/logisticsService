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






}