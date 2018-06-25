<?php
/**
 * 订单 pay model.
 * Auther: josy
 * Date: 17/7/11
 * Time: 上午9:59
 */


class Order_PayModel
{
    /**
     * @var string  默认的表名
     */

    public $dbh = null;

    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null)
    {
        $this->tranType=array(
            11=> '普通转账',
            12=> '资金初始化',
            13=> '利息分配',
            14=> '手续费分配',
            15=> '交易转账',
            16=> '调账',
            21=> '公共利息收费账户转账',
            22=> '公共调账账户外部转账',
            23=> '出入金',
        );
        $this->loanFlag = array('C'=>'入','D'=>'出');
        $this->dbh = $dbh;
        $this->pay = Hprose\Client::create("http://172.19.0.25:8003/api",false);
       // $this->pay =  rpcClient('service-pay/api');
    }
    /**
     * @param int $companyNo 企业编号
     * @return array
     * @throws Yaf_Exception
     * 通过企业编号查询企业账户金额
     */
    public function getZhongxinAmount($companyNo){
        if ($companyNo == null){
            throw new Yaf_Exception(StatusCode::CLIENT_EMPTY_PARAMETER_STRING,StatusCode::CLIENT_EMPTY_PARAMETER_CODE);
        }
        //通过公司编号查询是否支持线上支付
        $sql = "select bankaccountno from gl_companies_account WHERE companies_id = $companyNo and status = 1";

        if (Yaf_Registry:: get("db") instanceof MySQL) {
            $bankaccountno = $this->dbh->select_one($sql);

            if ($bankaccountno){
                return $this->_getZhongxinAmount($bankaccountno);
            }else{
                throw new Yaf_Exception(StatusCode::CLIENT_DATA_NOT_EXISTS_STRING,StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
            }
        } else {
            throw new Yaf_Exception("db配置不对",StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
        }
    }

    /**
     * @param $bankaccountno
     * @return array
     * @throws Yaf_Exception
     * 通过银行卡号获取中信账户信息
     */
    private function _getZhongxinAmount($bankaccountno)
    {
        if(!$bankaccountno){
            $params = array('total_amount'=>'暂时无法查询','frozen_amount'=>'暂时无法查询','active_amount'=>'暂时无法查询');
            throw new Yaf_Exception(json_encode($params));
        }
        $arr['banktype'] = 'ZhongXinApi';
        $arr['method'] = 'UserBalance';
        $arr['data']=array(
            'subAccNo'=>$bankaccountno
        );

        try{
            $amount=$this->pay->paymentFunc($arr);
        }catch (Exception $exception){
            $params = array('total_amount'=>'暂时无法查询','frozen_amount'=>'暂时无法查询','active_amount'=>'暂时无法查询');
            throw new Yaf_Exception(json_encode($params));
        }
        if($amount['code'] == '0000' && $amount['data']['status'] == 'AAAAAAA'){
            $params = array();
            $params['total_amount'] = $amount['data']['list']['row']['SJAMT'];   //账户金额
            $params['frozen_amount'] = $amount['data']['list']['row']['DJAMT'];  //冻结金额
            $params['active_amount'] = $amount['data']['list']['row']['KYAMT'];  //可用余额
            $params['total_amount'] = $params['total_amount']>0?$params['total_amount']:"0.00";
            $params['frozen_amount'] = $params['frozen_amount']>0?$params['frozen_amount']:"0.00";
            $params['active_amount'] = $params['active_amount']>0?$params['active_amount']:"0.00";
        }else{
            $params = array('total_amount'=>'暂时无法查询','frozen_amount'=>'暂时无法查询','active_amount'=>'暂时无法查询');
            throw new Yaf_Exception(json_encode($params));
        }
        return $params;

    }

    /**
     * 获取中信账户交易详情
     * @param int $companyNo 企业编号
     * @return array
     * @throws Yaf_Exception
     * 通过企业编号查询企业账户金额
     */
    public function getZhongxinAmountDetail($companyNo,$params){
        if ($companyNo == null){
            throw new Yaf_Exception(StatusCode::CLIENT_EMPTY_PARAMETER_STRING,StatusCode::CLIENT_EMPTY_PARAMETER_CODE);
        }
        //通过公司编号查询是否支持线上支付
        $sql = "select bankaccountno from gl_companies_account WHERE companies_id = $companyNo and status = 1";

        if (Yaf_Registry:: get("db") instanceof MySQL) {
            $bankaccountno = $this->dbh->select_one($sql);

            if ($bankaccountno){
                return $this->_getZhongxinAmountDetail($bankaccountno,$params);
            }else{
                throw new Yaf_Exception(StatusCode::CLIENT_DATA_NOT_EXISTS_STRING,StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
            }
        } else {
            throw new Yaf_Exception("db配置不对",StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
        }
    }
   /**
     * @param $bankaccountno
     * @return array
     * @throws Yaf_Exception
     * 通过银行卡号获取中信账户交易详情
     */
    private function _getZhongxinAmountDetail($bankaccountno,$params)
    {
        if(!$bankaccountno){
            throw new Yaf_Exception(StatusCode::CLIENT_DATA_NOT_EXISTS_STRING,StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
        }

        //开始时间
        $startDate = $params['startDate']? date('Ymd',strtotime($params['startDate'])):date("Ymd",time()-7*24*3600);
        //结束时间
        $endDate = $params['endDate']? date('Ymd',strtotime($params['endDate'])):date("Ymd",time());
        //分页
        $page = $params['page']? $params['page']:1;

        $arr['banktype'] = 'ZhongXinApi';
        $arr['method'] = 'NologPrint';
        $arr['data']=array(
            'subAccNo'      => $bankaccountno,
            // 'queryType'     => '',
            // 'tranType'      => $tranType,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'startRecord'   => ($page-1)*5+1,
            'pageNumber'    => 5
        );

        try{
            $zhongxin=$this->pay->paymentFunc($arr);
            if($zhongxin['code'] == '0001'){//接口验证数据不成功
                $params['code'] = 500;
            }else if($zhongxin['data']['status'] != 'AAAAAAA'){
                $params['code'] = 404;
            }else{
                $detail = $zhongxin['data']['list']['row'];
                $params['code'] = 200;
                foreach ($detail as $k => $v) {
                    $detail[$k]['tranType'] = $this->tranType[$v['tranType']];
                    $detail[$k]['loanFlag'] = $this->loanFlag[$v['loanFlag']];
                    $detail[$k]['tranDate'] = date('Y-m-d',strtotime($v['tranDate']));
                }
            }
            $params['detail'] = $detail;

        }catch (Exception $exception){
            throw new Yaf_Exception(StatusCode::CLIENT_DATA_NOT_EXISTS_STRING,StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
        }
        return $params;
    }

    //直接交易，直接支付给卖家。代码里可以同时绑定多个订单，逻辑层不应该允许
    public function  directPay($payno)
    {
        $this->dbh->begin();
        if (!$payno)
            return ReturnResult::failed(StatusCode::PAY_NOID_ERROR_CODE, StatusCode::PAY_NOID_ERROR_STRING)->toJson();
        $main = $this->dbh->select_row("select * from payment_master where paymentno = '$payno' and isdel = 0 for update");

        if (count($main) == 0) { //单据不存在
            return ReturnResult::failed(StatusCode::PAY_NOEXIST_ERROR_CODE, StatusCode::PAY_NOEXIST_ERROR_CODE)->toJson();
        }

        if ($main['paystatus'] == 3) {
            return ReturnResult::failed(StatusCode::PAY_HASPAID_ERROR_CODE, StatusCode::PAY_HASPAID_ERROR_CODE)->toJson();
        }

        $payOrders = $this->dbh->select("select * from payment_order where paymentno = '$payno' and status != 4 for update");

        if (count($payOrders) == 0) {//缺少结算单详情
            return ReturnResult::failed(StatusCode::PAY_NODETAIL_ERROR_CODE, StatusCode::PAY_NODETAIL_ERROR_CODE)->toJson();
        }

        try {
            $orders = [];
            $payamount = 0;

            //遍历所有的相关的结算单
            foreach ($payOrders as $payOrder) {

                $payid = $payOrder['id'];
                $order = $this->dbh->select_row("select * from gl_order where id = '$payOrder[order_id]' for update");

                $orders[$payid] = $order;
                $payamount += $payOrder['freightamount'];//获取付款单下面所有结算单金额
            }

            if (round($payamount,2) != round($main['freightamount'],2)) {
                $this->dbh->rollback();//详情金额与制单金额不匹配
                return ReturnResult::failed(StatusCode::PAY_NOMATCH_ERROR_CODE, StatusCode::PAY_NOMATCH_ERROR_STRING)->toJson();
            }

            //冻结资金
            $amount = round($main['freightamount'],2);
            $params['banktype'] = 'ZhongXinApi';
            $params['method'] = 'PayAccount';
            $clmeno = date("YmdHis") . '_' . rand(1000, 9999); //流水号与冻结号
            $params['data'] = array(
                'clientID' => $clmeno,
                'payAccNo' => $main['pay_bankno'],//'3110210005941094134',
                'tranType' => 'BF',
                'recvAccNo' => $main['receive_bankno'],//'3110210005941095438',
                'recvAccNm' => $main['receive_companyname'],//'summer3公司',
                'tranAmt' => $amount,
                'freezeNo' => '',
                'ofreezeamt' => $amount,
                'memo' => $payno,
                'tranFlag' => 1,
            );

            try{
               // $freeze['code'] = '0000';
               // $freeze['data']['status'] = 'AAAAAAA';
                $freeze = $this->pay->paymentFunc($params);
            }catch (Exception $exception){
                $this->dbh->rollback();
                //银行接口返回错误
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING)->toJson();
            }

            if ($freeze['code'] == '0000' && $freeze['data']['status'] == 'AAAAAAA') {
                foreach ($payOrders as $payOrder) {
                    $payid = $payOrder['id'];
                    $order = $orders[$payid];
                    $paid = $payOrder['freightamount'];

                    $orderUpdate = [
                        'fact_freight' => $paid,
                        'status' => 5,
                        'pay_time' => '=NOW()',
                        'updated_at' => '=NOW()'
                    ];
                    //更新托运单信息
                    $this->dbh->update('gl_order', $orderUpdate, 'id = ' . $order['id']);
                }

                //更新结算单信息
                    $payUpdate = [
                        'status' => 2, //已支付
                        'updated_at' => '=NOW()',
                        'serialno' => $clmeno,
                        'pay_type' => 1
                    ];
                    $this->dbh->update('payment_order', $payUpdate, 'id = ' . $payid);

                //更新付款单信息
                $payMasterUpdate = [
                    'paystatus' => '3', //支付完成
                    'paystatustime' => '=NOW()',
                    'onlineno' => $clmeno,
                    'updatedat' => '=NOW()'
                ];
                $this->dbh->update('payment_master', $payMasterUpdate, "paymentno = '$payno' and isdel = 0");

                $this->dbh->commit();
                return ReturnResult::success()->toJson();


            } else {

                $this->dbh->rollback();
                //银行接口返回错误
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING . ':' . $freeze['data']['statusText'])->toJson();
            }
        } catch (Exception $e) {
            $this->dbh->rollback();
            //服务器数据库错误
            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
        }
    }

    /**
     * @param  string $payno 付款单号
     * @return string
     * 线下支付
     */
    public function  linePay($payno)
    {

        if (!$payno){
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_NOID_ERROR_CODE, StatusCode::PAY_NOID_ERROR_STRING)->toJson();
        }
        $main = $this->dbh->select_row("select * from payment_master where paymentno = '$payno' and isdel = 0");

        if (count($main) == 0) {
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_NOEXIST_ERROR_CODE, StatusCode::PAY_NOEXIST_ERROR_CODE)->toJson();
        }


        if ($main['paystatus'] == 3) {
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_HASPAID_ERROR_CODE, StatusCode::PAY_HASPAID_ERROR_CODE)->toJson();
        }

        $payOrders = $this->dbh->select("select * from payment_order where paymentno = '$payno' and status != 4");

        if (count($payOrders) == 0) {
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_NODETAIL_ERROR_CODE, StatusCode::PAY_NODETAIL_ERROR_CODE)->toJson();
        }

        $this->dbh->begin();
        try {

            $payamount = 0;

            foreach ($payOrders as $payOrder) {
                $payamount += $payOrder['freightamount'];//获取付款单下面所有结算单金额
            }

            if (round($payamount,2) != round($main['freightamount'],2)) {
                $this->dbh->rollback();//详情金额与制单金额不匹配
                return ReturnResult::failed(StatusCode::PAY_NOMATCH_ERROR_CODE, StatusCode::PAY_NOMATCH_ERROR_STRING)->toJson();
            }

            $clmeno = date("YmdHis") . '_' . rand(1000, 9999); //流水号与冻结号

            foreach ($payOrders as $payOrder) {
                $payid = $payOrder['id'];

                //更新结算单信息
                $payUpdate = [
                    'status' => 2, //已支付
                    'updated_at' => '=NOW()',
                    'serialno' => $clmeno,
                    'pay_type' => 2
                ];
                $this->dbh->update('payment_order', $payUpdate, 'id = ' . $payid);
            }
            //更新付款单信息
            $payMasterUpdate = [
                'pay_type' => '2', //支付完成
                'paystatus' => '2', //待审核
                'paystatustime' => '=NOW()',
                'updatedat' => '=NOW()'
            ];
            $this->dbh->update('payment_master', $payMasterUpdate, "paymentno = '$payno' and isdel = 0");

            $this->dbh->commit();
            return ReturnResult::success()->toJson();

        } catch (Exception $e) {
            $this->dbh->rollback();
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
        }
    }
}