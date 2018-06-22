<?php

/**
 * 付款单的订单
 */
class Payment_OrderController extends Rpc
{
    public function init()
    {
        parent::init();
    }

    /**
     * @param string $orderNo 订单编号
     * @param bool $isOther 是否获得当前收付款单下的所有订单和附件文件
     * @return string
     * 获得订单的所有信息
     */
    public function getListFunc($params){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }


    public function getInfoFunc($id){
        $L = new Payment_OrderModel(Yaf_Registry::get("db"));
        $data = $L->getInfo($id);
        return $data;
    }


    //创建结算单
    public function createpayFunc($params){
        try{
            $order = Payment_OrderModel::getInstance()->addPaymentOrder($params);
            return ReturnResult::success($order)->toJson();
        }catch (Exception $exception){
            return ReturnResult::failed($exception->getCode(),$exception->getMessage())->toJson();
        }        
    }


    //list结算单
    public function getpaylistFunc($params){
           return  Payment_OrderModel::getInstance()->getpaylist($params);
    }
}