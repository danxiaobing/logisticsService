<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/8 14:22
 * Author: daley <wanghuihui@chinayie.com>
 * 付款单
 */
class Order_payController extends Rpc
{
    public function init()
    {
        parent::init();
    }


    /**
     * @param $companyNo
     * @return string
     * 通过企业编号查询企业账户金额
     */
    public function getZhongxinAmountFunc($companyNo){
        try{
            $Order_Pay = new Order_PayModel(Yaf_Registry::get("db"));
            $result = $Order_Pay->getZhongxinAmount($companyNo);
            return ReturnResult::success($result)->toArray();
        }catch (Yaf_Exception $yaf_Exception){
            return ReturnResult::failed($yaf_Exception->getCode(),$yaf_Exception->getMessage())->toArray();
        }
    }
    /**
     * @param $companyNo
     * @return string
     * 通过企业编号查询企业账户交易详情
     */
    public function getZhongxinAmountDetailFunc($companyNo,$params){
        try{
            $Order_Pay = new Order_PayModel(Yaf_Registry::get("db"));
            $result = $Order_Pay->getZhongxinAmountDetail($companyNo,$params);
            return ReturnResult::success($result)->toArray();
        }catch (Yaf_Exception $yaf_Exception){
            return ReturnResult::failed($yaf_Exception->getCode(),$yaf_Exception->getMessage())->toArray();
        }
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $logParams     收付款单操作日志参数
     * @return mixed
     * 线上支付
     */
    public function onLinePayFunc($paymentNo,$masterParams,$logParams){
        try{

            $Order_Pay = new Order_PayModel(Yaf_Registry::get("db"));
            //直接交易，直接支付给卖家。代码里可以同时绑定多个订单，逻辑层不应该允许
            $result = $Order_Pay->directPay($paymentNo);

            $Payment_Master = new Payment_MasterModel(Yaf_Registry::get("db"));
            $paymentInfo = $Payment_Master->getPaymentInfo($paymentNo,false);
            if (isset($paymentInfo['paystatus']) && $paymentInfo['paystatus'] == 1){
                unset($masterParams['validator']);
                unset($masterParams['validatortime']);
            }
            //首付款编号
            $Payment_Master->updatedMasterAndLog($paymentNo,$masterParams,$logParams);
            return $result;
        }catch (Exception $yaf_Exception){
            return ReturnResult::failed($yaf_Exception->getCode(),$yaf_Exception->getMessage().',添加失败')->toJson();
        }
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $logParams     收付款单操作日志参数
     * @return string
     * 线下支付
     */
    public function linePayFunc($paymentNo,$masterParams,$logParams){
        try{
            $Order_Pay = new Order_PayModel(Yaf_Registry::get("db"));
            $result = $Order_Pay->linePay($paymentNo);
            $Payment_Master = new Payment_MasterModel(Yaf_Registry::get("db"));
            $paymentInfo = $Payment_Master->getPaymentInfo($paymentNo,false);
            if (isset($paymentInfo['paystatus']) && $paymentInfo['paystatus'] == 1){
                unset($masterParams['validator']);
                unset($masterParams['validatortime']);
            }
            //首付款编号
            $Payment_Master->updatedMasterAndLog($paymentNo,$masterParams,$logParams);
            return $result;
        }catch (Exception $yaf_Exception){
            return ReturnResult::failed($yaf_Exception->getCode(),$yaf_Exception->getMessage().'，添加失败')->toJson();
        }
    }


    /**
     * @param $paymentNo
     * @param $masterParams
     * @param $logParams
     * @return mixed
     * @throws Yaf_Exception
     * 确认支付
     */
    public function confirmPaymentFunc($paymentNo,$masterParams,$logParams){
        try{
            $paymentNo = Payment_MasterModel::getInstance()->confirmPayment($paymentNo,$masterParams,$logParams);
            return ReturnResult::success(['paymentNo'=>$paymentNo])->toJson();
        }catch (Exception $yaf_Exception){
            return ReturnResult::failed(StatusCode::SERVER_ERROR_CODE,$yaf_Exception->getMessage())->toJson();
        }
    }


}