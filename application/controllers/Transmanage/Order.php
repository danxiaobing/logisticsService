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
        $L = new Transmanage_OrderModel(Yaf_Registry::get("db"));
        $data = $L->searchOrder($params);
        return $data;
    }


}