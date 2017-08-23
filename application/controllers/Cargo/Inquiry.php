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
    public function getGoodsInquirInfoFunc($id){
        $L = new Cargo_InquiryModel(Yaf_Registry::get("db"));
        $data = $L->getGoodsInquiryInfo($id);
        return $data;
    }

    /**
     * 新增货源询价单记录信息
     */
    public function addInquiryInfoFunc($params){
        $L = new Cargo_InquiryModel(Yaf_Registry::get("db"));
        $data = $L->addInquiryInfo($params);
        return $data;
    }

    /**
     * 修改货源询价单信息
     */
    public function updataInquiryFunc($id,$params){
        $L = new Cargo_InquiryModel(Yaf_Registry::get("db"));
        $data = $L->updataInquiry($id,$params);
        return $data;

    }
}