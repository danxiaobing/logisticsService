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
        $cityArr = array_column($result['city'],'city','cityid');//城市数据 cityid-city 
        $provinceArr = array_column($result['province'],'province','provinceid');//省数据 provinceid-province

        $s_index = $result['data']['start_city_id'];//起始城市id
        $e_index = $result['data']['end_city_id'];//起始城市id
        $ps_index = $result['data']['start_provice_id'];//起始省id
        $pe_index = $result['data']['end_provice_id'];//目的省id

        $result['provincestart'] = $provinceArr[$ps_index];//起始省
        $result['provinceend'] = $provinceArr[$pe_index];//目的省
        $result['citystart'] = $cityArr[$s_index];//起始城市
        $result['cityend'] = $cityArr[$e_index];//目的城市

        unset($result['city']);
        unset($result['province']);
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

}