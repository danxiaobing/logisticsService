<?php
/**
 * @author  amor
 * @date    2017-8-1
 * @version $Id$
 */

class Examine_CarrierController extends Rpc {


    public function init() {
        parent::init();

    }

    /**
     * 获取供应商列表
     * @param array $params
     * @return array $data
     */
    public function getCarrierList($params){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->getCarrierList($params);
        return $data;
    }

    /**
     * 获取供应商
     * @param int $params
     * @return array $data
     */
    public function getCarrier($id){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->getCarrierList(intval($id));
        return $data;
    }
}
