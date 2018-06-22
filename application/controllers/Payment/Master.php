<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/8 14:22
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 付款单
 */
class Payment_MasterController extends Rpc
{
    public function init()
    {
        parent::init();
    }

    /**
     * @param array $masterParams  收付款单参数
     * @param array $filesParams   收付款单-附件表参数
     * @param array $orderParams   收付款单-付款单关联订单表参数
     * @param array $logParams     收付款单操作日志参数
     * @return string
     * 添加收付款单
     */
    public function addMasterAndFilesAndOrderAndLogFunc($masterParams,$filesParams,$orderParams,$logParams)
    {
        //获得付款单编号
        $masterParams['paymentno'] = isset($masterParams['paymentno'])?$masterParams['paymentno']:PublicMethodsModel::getPaymentMasterNumber('ZJ');
        try{
            //首付款编号
            $paymentNo = Payment_MasterModel::getInstance()->addMasterAndFilesAndOrderAndLog($masterParams,$filesParams,$orderParams,$logParams);
            return ReturnResult::success(['paymentNo'=>$paymentNo])->toJson();
        }catch (Exception $exception){
            return ReturnResult::failed(StatusCode::SERVER_ERROR_CODE,StatusCode::SERVER_ERROR_STRING.',添加失败')->toJson();
        }

    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $filesParams   收付款单-附件表参数
     * @param array $orderParams   收付款单-付款单关联订单表参数
     * @param array $logParams     收付款单操作日志参数
     * @return mixed
     * 更新收付款单
     */
    public function updatedMasterAndFilesAndOrderAndLogFunc($paymentNo,$masterParams,$filesParams,$orderParams,$logParams)
    {
        try{
            //首付款编号
            $paymentNo = Payment_MasterModel::getInstance()->updatedMasterAndFilesAndOrderAndLog($paymentNo,$masterParams,$filesParams,$orderParams,$logParams);
            return ReturnResult::success(['paymentNo'=>$paymentNo])->toJson();
        }catch (Exception $yaf_Exception){
            return ReturnResult::failed(StatusCode::SERVER_ERROR_CODE,StatusCode::SERVER_ERROR_STRING.',添加失败')->toJson();
        }
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $logParams     收付款单操作日志参数
     * @return mixed
     * 更新收付款单
     */
    public function updatedMasterAndLogFunc($paymentNo,$masterParams,$logParams)
    {
        try{
            //首付款编号
            $paymentNo = Payment_MasterModel::getInstance()->updatedMasterAndLog($paymentNo,$masterParams,$logParams);
            return ReturnResult::success(['paymentNo'=>$paymentNo])->toJson();
        }catch (Exception $yaf_Exception){
            return ReturnResult::failed($yaf_Exception->getCode(),$yaf_Exception->getMessage().',操作失败')->toJson();
        }
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param string $companyName    收付款单企业人
     * @return mixed
     * 查找单个收付款单
     */
    public function findPaymentMasterFunc($paymentNo = null,$companyName =null){
        try{
            $master = Payment_MasterModel::getInstance()->findMaster($paymentNo,$companyName);
            return ReturnResult::success($master)->toJson();
        }catch (Exception $exception){
            return ReturnResult::failed(StatusCode::CLIENT_DATA_NOT_EXISTS_CODE,StatusCode::CLIENT_DATA_NOT_EXISTS_STRING.',查找失败,'.$exception->getMessage())->toJson();
        }

    }

    /**
     * @param array $search 查询参数
     * @return mixed
     * 查找收付款单列表
     */
    public function findListPaymentMasterFunc($search){
        try{
            $listPaymentMaster = Payment_MasterModel::getInstance()->getListPayment($search);
            return ReturnResult::success($listPaymentMaster)->toJson();
        }catch (Exception $exception){
            return ReturnResult::failed(StatusCode::CLIENT_DATA_NOT_EXISTS_CODE,StatusCode::CLIENT_DATA_NOT_EXISTS_STRING.',查找失败')->toJson();
        }

    }

    /**
     * 获得某个订单的已支付保证金金额
     * @params strint $orderno  订单编号
     * return  int
     */
    public function getOrderPayBondamountFunc($orderno){
        try{
            $orderPayBondamount = Payment_OrderModel::getInstance()->getOrderPayBondamount($orderno);
            return ReturnResult::success($orderPayBondamount)->toJson();
        }catch (Exception $exception){
            return ReturnResult::failed(StatusCode::CLIENT_DATA_NOT_EXISTS_CODE,StatusCode::CLIENT_DATA_NOT_EXISTS_STRING.',查找失败')->toJson();
        }
    }

    /**
     * @param $companyNo
     * @return string
     * 通过企业编号查询企业账户金额
     */
    public function getZhongxinAmountFunc($companyNo){
        try{
            $result = Order_PayModel::getInstance()->getZhongxinAmount($companyNo);
            return ReturnResult::success($result)->toJson();
        }catch (Yaf_Exception $yaf_Exception){
            return ReturnResult::failed($yaf_Exception->getCode(),$yaf_Exception->getMessage())->toJson();
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
     * @param string $paymentNo 收付款单编号
     * @param $masterParams
     * @param $logParams
     * @return mixed
     * @throws Yaf_Exception
     * 驳回付款单
     */
    public function rejectPaymentFunc($paymentNo,$masterParams,$logParams){
        try{
            $paymentNo = Payment_MasterModel::getInstance()->rejectPayment($paymentNo,$masterParams,$logParams);
            return ReturnResult::success(['paymentNo'=>$paymentNo])->toJson();
        }catch (Exception $yaf_Exception){
            return ReturnResult::failed(StatusCode::SERVER_ERROR_CODE,$yaf_Exception->getMessage())->toJson();
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