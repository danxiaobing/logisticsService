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



}
