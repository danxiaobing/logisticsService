<?php

/**
 * 中信出金审核
 * Created by PhpStorm.
 * User: amor
 * Date: 2018/6/21
 * Time: 10:29
 */
class Capital_PaymentController extends Rpc
{


    /**
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        #   Yaf_Dispatcher::getInstance()->disableView();
    }

    public function getPaymentListFunc($params)
    {
        $Cp = new Capital_PaymentModel(Yaf_Registry::get("db"));
        $data = $Cp->getPaymentList($params);
        return $data;
    }


    public function getPaymentDetailFunc($id)
    {
        $Cp = new Capital_PaymentModel(Yaf_Registry::get("db"));
        $data = $Cp->getPaymentDetail($id);
        return $data;

    }


}