<?php

/**
 * 付款单的订单
 */
class Payment_MasterController extends Rpc
{
    public function init()
    {
        parent::init();
    }


    public function getPaymentListFunc($params){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }

    public function getInfoFunc($id){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->getInfo($id);
        return $data;
    }

    public function getFileFunc($no){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->getFile($no);
        return $data;
    }

    public function addfileFunc($data){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->addfile($data);
        return $data;
    }

    public function updatePaymentMasterFunc($paymentno, $data){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->updatePaymentMaster($paymentno, $data);
        return $data;
    }

    public function getPaymentInfoFunc($paymentNo,$isOther){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->getPaymentInfo($paymentNo,$isOther);
        return $data;
    }


    /**
     * @param string $paymentNo    收付款单编号
     * @param string $companyName    收付款单企业人
     * @return mixed
     * 查找单个收付款单
     */
    public function findPaymentMasterFunc($paymentNo = null,$companyName =null){
        try{
            $master = new Payment_MasterModel(Yaf_Registry::get("db"));
            $result = $master->findMaster($paymentNo,$companyName);
            return ReturnResult::success($result)->toArray();
        }catch (Exception $exception){
            return ReturnResult::failed(StatusCode::CLIENT_DATA_NOT_EXISTS_CODE,StatusCode::CLIENT_DATA_NOT_EXISTS_STRING.',查找失败,'.$exception->getMessage())->toArray();
        }

    }


    public function affirmFunc($params){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->affirm($params);
        return $data;
    }

    public function backPaymentMasterStatusFunc($params){
        $L = new Payment_MasterModel(Yaf_Registry::get("db"));
        $data = $L->backPaymentMasterStatus($params);
        return $data;
    }

    /**
     * 修改收款单账户
     * @param string $paymentno
     * @param int $paystatus
     * @return array
     */
    public function updatePaymentStatusFunc($paymentno,$paystatus){
        try{
            $master = new Payment_MasterModel(Yaf_Registry::get("db"));
            $result = $master->updatePaymentStatus($paymentno,$paystatus);
            return ReturnResult::success($result)->toArray();
        }catch (Exception $exception){
            return ReturnResult::failed(StatusCode::CLIENT_DATA_NOT_EXISTS_CODE,$exception->getMessage())->toArray();
        }
    }
}