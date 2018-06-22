<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/8 14:22
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
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
    public function getOrderPayListFunc($orderNo,$isOther = true){
        try{
            $order = Payment_OrderModel::getInstance()->getOrderPayList($orderNo,$isOther);
            return ReturnResult::success($order)->toJson();
        }catch (Exception $exception){
            return ReturnResult::failed($exception->getCode(),$exception->getMessage())->toJson();
        }
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