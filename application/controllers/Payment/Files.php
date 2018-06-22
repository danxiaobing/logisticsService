<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/8 14:22
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 付款单
 */
class Payment_FilesController extends Rpc
{
    public function init()
    {
        parent::init();
    }

    public function addPaymentFilesFunc($params){
        return Payment_FilesModel::getInstance()->addPaymentFiles($params);
    }
}