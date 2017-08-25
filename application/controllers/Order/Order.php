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
     * 搜索
     * @param array $paramsa
     * @author amor
     */
    public function searchOrderFunc($params){
        $L = new Order_OrderModel(Yaf_Registry::get("db"));
        $data = $L->searchOrder($params);
        return $data;
    }






}