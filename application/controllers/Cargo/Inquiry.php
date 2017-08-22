<?php
/**
 * 货主--询价管理
 * Class Inquiry
 * @author  Daley
 * @date    2017-8-22
 * @version $Id$
 */
class Cargo_InquiryController extends Rpc {

    public function init() {
        parent::init();
    }

    /**
     * 货源询价管理
     */
    public function getGoodsInquiryListFunc($params){

        $L = new Cargo_InquiryModel(Yaf_Registry::get("db"));
        $data = $L->getGoodsInquiryList($params);
        return $data;
    }

    /**
     * 获取货源询价单详情
     */
    public function getGoodsInquirInfoFunc($params){
        $L = new Cargo_InquiryModel(Yaf_Registry::get("db"));
        $data = $L->getGoodsInquirInfo($params);
        return $data;
    }
}