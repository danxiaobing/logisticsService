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

    /*获取单个托运单详情*/
    public function getOrderInfoFunc($orderid){
        $O = new Transmanage_OrderModel(Yaf_Registry::get("db"));
        $result = $O->getOrderInfo($orderid); 
        $cityArr = array_column($result['city'],'city','cityid');//城市数据 cityid-city 
        $s_index = $result['data']['start_city_id'];//起始城市id
        $e_index = $result['data']['end_city_id'];//起始城市id
        $result['citystart'] = $cityArr[$s_index];//起始城市
        $result['cityend'] = $cityArr[$e_index];//目的城市
        unset($result['city']);
        return $result;
    }

}