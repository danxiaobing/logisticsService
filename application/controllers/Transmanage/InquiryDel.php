<?php
/**
 * 询价单管理
 * @author  Andy
 * @date    2017-8-121
 * @version $Id$
 */

class Transmanage_InquiryDelController extends Rpc {

    public function init() {
        parent::init();
    }

    /**
     * 获取数据
     */
   public function getInquiryListFunc($search)
    {
        $L = new Transmanage_InquiryDelModel(Yaf_Registry::get("db"));
        return $L->getInquiryList($search);
    }

    /*获取询价单基本信息*/
    public function getGoodsInfoFunc($id){
        $L = new Transmanage_InquiryDelModel(Yaf_Registry::get("db"));
        return $L->getGoodsInfo($id);        
    }

    /*生成询价单、询价日志*/
    public function addReceiptFunc($data=array(),$price){
        $L = new Transmanage_InquiryDelModel(Yaf_Registry::get("db"));
        return $L->addReceipt($data,$price);  
    }

}
