<?php

class Api_ApiController extends Rpc {

    public function init() {
        parent::init();
        
    }


    /**
     * 回程车-专线车查询
     * @param $params
     * @author daley
     * @return array
     */
    public function getBackAndLineCarPageFunc($params)
    {
        $L = new Api_ApiModel(Yaf_Registry::get("db"));
        $data = $L->getBackAndLineCarPage($params);
        return $data;
    }

    /**
     * 根据运单号检测运单是否存在
     */
    public function checkConsignsByNumberFunc($params){

        $L = new Api_ApiModel(Yaf_Registry::get("db"));
        $data = $L->checkConsignsByNumber($params);
        return $data;
    }

    /**
     * 匹配运费价格
     */
    public function matchingFreightPriceFunc($params){

        $L = new Api_ApiModel(Yaf_Registry::get("db"));
        $data = $L->matchingFreightPrice($params);
        return $data;

    }



}
