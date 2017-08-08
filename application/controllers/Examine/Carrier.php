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
     * 获取承运商列表
     * @param array $params
     * @return array $data
     */
    public function getCarrierListFunc($params,$pid=''){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->getCarrierList($params,$pid);
        return $data;
    }

    /**
     * 获取承运商
     * @param int $params
     * @return array $data
     */
    public function getCarrierFunc($id){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->getCarrier(intval($id));
        return $data;
    }

    /**
     * 修改
     * @param array  $status
     * @param integer $where
     * @return array
     */
    public function examineCarrierFunc($status,$where){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->examineCarrier($status,$where);
        return $data;
    }

    /**
     * 审核
     * @param  integer $id
     * @return array   $data
     */
    public  function showfileFunc($id){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->showfile($id);
        return $data;
    }

    public function updateCarrierFunc($params,$id){
        $Carrier = new Examine_CarrierModel(Yaf_Registry::get("db"));
        $data = $Carrier->updateCarrier($params,$id);
        return $data;
    }

}
