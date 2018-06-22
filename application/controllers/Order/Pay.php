<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/8 14:22
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
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
            //判断是否是担保交易，需冻结在卖家，可以同时绑定多个订单
            $payment = Payment_MasterModel::getInstance()->getPaymentInfo($paymentNo);
            if (count($payment['paymentOrderList']) == 1 ){
                $order = new Order_CurdModel();
                $data = $order->getInfo( $payment['paymentOrderList'][0]['orderno']);
                if ($data != null && $data['isassure'] == 2){  //是否是担保交易
                    $result = Order_PayModel::getInstance()->guarantePay($paymentNo);
                }else{
                    //直接交易，直接支付给卖家。代码里可以同时绑定多个订单，逻辑层不应该允许
                    $result = Order_PayModel::getInstance()->directPay($paymentNo);
                }
            }else{
                //直接交易，直接支付给卖家。代码里可以同时绑定多个订单，逻辑层不应该允许
                $result = Order_PayModel::getInstance()->directPay($paymentNo);
            }

            $paymentInfo = Payment_MasterModel::getInstance()->getPaymentInfo($paymentNo,false);
            if (isset($paymentInfo['paystatus']) && $paymentInfo['paystatus'] == 1){
                unset($masterParams['validator']);
                unset($masterParams['validatortime']);
            }
            //首付款编号
            Payment_MasterModel::getInstance()->updatedMasterAndLog($paymentNo,$masterParams,$logParams);
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
            $result = Order_PayModel::getInstance()->linePay($paymentNo);
            $paymentInfo = Payment_MasterModel::getInstance()->getPaymentInfo($paymentNo,false);
            if (isset($paymentInfo['paystatus']) && $paymentInfo['paystatus'] == 1){
                unset($masterParams['validator']);
                unset($masterParams['validatortime']);
            }
            //首付款编号
            Payment_MasterModel::getInstance()->updatedMasterAndLog($paymentNo,$masterParams,$logParams);
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