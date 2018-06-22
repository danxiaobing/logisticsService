<?php
/**
 * 订单 pay model.
 * tableName = 'payment_master';
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
        $this->dbh = $dbh;
        $this->pay = Hprose\Client::create("http://172.19.0.25:8003//api",false);
       // $this->pay =  rpcClient('service-pay/api');
    }
    /*protected function __construct() {
        self::$tableName = 'payment_master';
       //$this->pay = Hprose\Client::create("http://192.168.18.211:8003/api",false);
        $this->pay =  rpcClient('service-pay/api');
    }*/

    //担保交易，需冻结在卖家，可以同时绑定多个订单
    public function  guarantePay($payno)
    {
        self::getInstance()->dbh->begin();
        if (!$payno)
            return ReturnResult::failed(StatusCode::PAY_NOID_ERROR_CODE, StatusCode::PAY_NOID_ERROR_STRING)->toJson();
        $main = self::getInstance()->dbh->select_row("select * from payment_master where paymentno = '$payno' and isdel = 0 FOR UPDATE");

        if ($main == null) {
            return ReturnResult::failed(StatusCode::PAY_NOEXIST_ERROR_CODE, StatusCode::PAY_NOEXIST_ERROR_STRING)->toJson();
        }

        if ($main['paystatus'] == 3) {
            return ReturnResult::failed(StatusCode::PAY_HASPAID_ERROR_CODE, StatusCode::PAY_HASPAID_ERROR_STRING)->toJson();
        }

        $payOrders = self::getInstance()->dbh->select("select * from payment_order where paymentno = '$payno' and status != 4 FOR UPDATE");

        if (count($payOrders) == 0) {
            return ReturnResult::failed(StatusCode::PAY_NODETAIL_ERROR_CODE, StatusCode::PAY_NODETAIL_ERROR_STRING)->toJson();
        }


        try {
            $orders = [];

            $payamount = 0;


            foreach ($payOrders as $payOrder) {
                $payid = $payOrder['sysno'];
                $order = self::getInstance()->dbh->select_row("select * from order_master where sysno = '$payOrder[order_id]' for update");
                //如果确认复核后，则应付金额不是sum_money 是 reviewmoney
                if($order['reviewstatus'] == 2){
                    $orderMoney = $order['reviewmoney'] - $order['paid_money']; //未付金额
                }else{
                    $orderMoney = $order['sum_money'] - $order['paid_money']; //未付金额
                }
        //        $orderMoney = $order['sum_money'] - $order['paid_money'];
                $payOrderMoney = $payOrder['bondamount'] + $payOrder['goodsamount'];
//                if (($orderMoney-$payOrderMoney) < 0 && round($orderMoney,2) != round($payOrderMoney,2)) {
//                    self::getInstance()->dbh->rollback();
//                    return ReturnResult::failed(StatusCode::PAY_OVERLIMIT_ERROR_CODE, StatusCode::PAY_OVERLIMIT_ERROR_STRING . ':' . $order['orderno'])->toJson();
//                }

                $orders[$payid] = $order;
                $payamount += $payOrder['bondamount'] + $payOrder['goodsamount'];
            }

            if (round($payamount,2) != round($main['bondamount'] + $main['goodsamount'],2)) {
                self::getInstance()->dbh->rollback();
                return ReturnResult::failed(StatusCode::PAY_NOMATCH_ERROR_CODE, StatusCode::PAY_NOMATCH_ERROR_STRING)->toJson();
            }

            //冻结资金
            $amount = payFormat($main['bondamount'] + $main['goodsamount']);
            $params['banktype'] = 'ZhongXinApi';
            $params['method'] = 'PayAccount';
            $clmeno = date("YmdHis") . '_' . rand(1000, 9999); //流水号与冻结号
            $params['data'] = array(
                'clientID' => $clmeno,
                'payAccNo' => $main['pay_bankno'],//'3110210005941094134',
                'tranType' => 'BS',
                'recvAccNo' => $main['receive_bankno'],//'3110210005941095438',
                'recvAccNm' => $main['receive_companyname'],//'summer3公司',
                'tranAmt' => $amount,
                'freezeNo' => '',
                'ofreezeamt' => $amount,
                'memo' => $payno,
                'tranFlag' => 1,
            );

            try{
                $freeze =  self::getInstance()->pay->paymentFunc($params);
            }catch (Exception $exception){
                self::getInstance()->dbh->rollback();
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING)->toJson();
            }

            if ($freeze['code'] == '0000' && $freeze['data']['status'] == 'AAAAAAA') {
                foreach ($payOrders as $payOrder) {
                    $payid = $payOrder['sysno'];
                    $order = $orders[$payid];

                    $paid = $payOrder['bondamount'] + $payOrder['goodsamount'] + $order['paid_money'];

                    //判断是否是退款
                    if (isset($main['is_refund']) && $main['is_refund'] == 1){
                        $paid = $order['paid_money'] - $payOrder['bondamount'] - $payOrder['goodsamount'];
                    }

                    $orderUpdate = [
                        'paid_money' => $paid,
                        'updatedat' => '=NOW()'
                    ];
                    self::getInstance()->dbh->update('order_master', $orderUpdate, 'sysno = ' . $order['sysno']);

                    $orderRow = self::getInstance()->dbh->select_row("select * from `order_master`  where sysno = " . $payOrder['order_id']);

                    //相等的话，付款步骤结束
                    $currentstep =  $orderRow['currentstep'];
                    if( ($currentstep == 1 && ( $orderRow['paid_money']  >=   round($orderRow['sum_money'] * ( 1 -   $orderRow['moreless']/100  ) ,2) ) )|| ( $orderRow['reviewstatus'] == 2 && $currentstep > 1 && $orderRow['paid_money']  ==   $orderRow['reviewmoney']) ){

                        $orderno = $orderRow['orderno'];
                        $ordertype = $orderRow['ordertype'];
                        $order_id = $orderRow['sysno'];

                        if($currentstep == 1){
                            if($ordertype == '1')
                                $status_code = 'NOR001';
                            else if($ordertype == '2')
                                $status_code = 'ROD001';
                            else
                                $status_code = 'SPC001';
                        }else{
                            if($ordertype == '1')
                                $status_code = 'NOR003';
                            else if($ordertype == '2')
                                $status_code = 'ROD003';
                            else
                                $status_code = 'SPC003';
                        }


                        $status_row = self::getInstance()->dbh->select_row("select * from order_status where orderno='$orderno' and statuscode='$status_code'");

                        if(count($status_row) == 0){
                            self::getInstance()->dbh->rollback();
                            return ReturnResult::failed(StatusCode::ORDER_NOFLOW_ERROR_CODE,StatusCode::ORDER_NOFLOW_ERROR_STRING)->toJson();
                        }

                        $resArr = array(
                            'execstatus' => 3,
                            'updated_at' =>  '=NOW()',
                            'execd_at' => '=NOW()'
                        );
                        $where = 'sysno = ' . $status_row['sysno'];

                        $res  = self::getInstance()->dbh->update('order_status', $resArr, $where);
                        if(!$res){
                            self::getInstance()->dbh->rollback();
                            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();

                        }

                        $sql = "select count(*) from order_status where order_id = '$order_id' and step = $status_row[step] and execstatus < 3";
                        $cnt = self::getInstance()->dbh->select_one($sql);

                        if($cnt ==0) {
                            $step = $status_row['step'] + 1;
                            $sql = "select count(*) from order_status where order_id = '$order_id' and step = '$step' and execstatus < 2";
                            $next_cnt = self::getInstance()->dbh->select_one($sql);
                            if ($next_cnt > 0) {
                                $mainInput['currentstep'] = $step;

                                $input = ['execstatus' => 2, 'updated_at' => '=NOW()'];
                                $where = 'order_id = ' . $order_id . " and step = " . $step;
                                $res = self::getInstance()->dbh->update('order_status', $input, $where);
                                if (!$res) {
                                    self::getInstance()->dbh->rollback();
                                    return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
                                }
                            } else {
                                $mainInput['orderstatus'] = 4; //最终完成
                            }

                            $where = 'sysno = ' . $order_id;

                            $res = self::getInstance()->dbh->update('order_master', $mainInput, $where);
                            if (!$res) {
                                self::getInstance()->dbh->rollback();
                                return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
                            }
                        }
                    }

                    $payUpdate = [
                        'status' => 1, //已支付未解冻
                        'linktime' => '=NOW()',
                        'serialno' => $clmeno,
                        'paytype' => 1
                    ];
                    self::getInstance()->dbh->update('payment_order', $payUpdate, 'sysno = ' . $payid);
                }

                $payMasterUpdate = [
                    'paystatus' => '3', //支付完成
                    'paystatustime' => '=NOW()',
                    'onlineno' => $clmeno,
                    'updatedat' => '=NOW()'
                ];
                self::getInstance()->dbh->update('payment_master', $payMasterUpdate, "paymentno = '$payno' and isdel = 0");

                self::getInstance()->dbh->commit();
                return ReturnResult::success()->toJson();


            } else {
//                var_dump($freeze);
                self::getInstance()->dbh->rollback();
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING . ':' . $freeze['data']['statusText'])->toJson();
            }
        } catch (Exception $e) {
            self::getInstance()->dbh->rollback();
            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
        }
    }

    //直接交易，直接支付给卖家。代码里可以同时绑定多个订单，逻辑层不应该允许
    public function  directPay($payno)
    {
        self::getInstance()->dbh->begin();
        if (!$payno)
            return ReturnResult::failed(StatusCode::PAY_NOID_ERROR_CODE, StatusCode::PAY_NOID_ERROR_STRING)->toJson();
        $main = self::getInstance()->dbh->select_row("select * from payment_master where paymentno = '$payno' and isdel = 0 for update");
        if (count($main) == 0) {
            return ReturnResult::failed(StatusCode::PAY_NOEXIST_ERROR_CODE, StatusCode::PAY_NOEXIST_ERROR_CODE)->toJson();
        }


        if ($main['paystatus'] == 3) {
            return ReturnResult::failed(StatusCode::PAY_HASPAID_ERROR_CODE, StatusCode::PAY_HASPAID_ERROR_CODE)->toJson();
        }

        $payOrders = self::getInstance()->dbh->select("select * from payment_order where paymentno = '$payno' and status != 4 for update");

        if (count($payOrders) == 0) {
            return ReturnResult::failed(StatusCode::PAY_NODETAIL_ERROR_CODE, StatusCode::PAY_NODETAIL_ERROR_CODE)->toJson();
        }

        try {
            $orders = [];

            $payamount = 0;


            foreach ($payOrders as $payOrder) {
                $payid = $payOrder['sysno'];
                $order = self::getInstance()->dbh->select_row("select * from order_master where sysno = '$payOrder[order_id]' for update");
                //如果确认复核后，则应付金额不是sum_money 是 reviewmoney
                if($order['reviewstatus'] == 2){
                    $orderMoney = $order['reviewmoney'] - $order['paid_money']; //未付金额
                }else{
                    $orderMoney = $order['sum_money'] - $order['paid_money']; //未付金额
                }

                //$orderMoney = $order['sum_money'] - $order['paid_money'];
                $payOrderMoney = $payOrder['bondamount'] + $payOrder['goodsamount'];
//                if (($orderMoney-$payOrderMoney) < 0 && round($orderMoney,2) != round($payOrderMoney,2)) {
//                    self::getInstance()->dbh->rollback();
//                    return ReturnResult::failed(StatusCode::PAY_OVERLIMIT_ERROR_CODE, StatusCode::PAY_OVERLIMIT_ERROR_STRING . ':' . $order['orderno'])->toJson();
//                }

                $orders[$payid] = $order;
                $payamount += $payOrder['bondamount'] + $payOrder['goodsamount'];
            }

            if (round($payamount,2) != round($main['bondamount'] + $main['goodsamount'],2)) {
                self::getInstance()->dbh->rollback();
                return ReturnResult::failed(StatusCode::PAY_NOMATCH_ERROR_CODE, StatusCode::PAY_NOMATCH_ERROR_STRING)->toJson();
            }

            //冻结资金
            $amount = payFormat($main['bondamount'] + $main['goodsamount']);
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
                $freeze =self::getInstance()->pay->paymentFunc($params);
            }catch (Exception $exception){
                self::getInstance()->dbh->rollback();
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING)->toJson();
            }

            if ($freeze['code'] == '0000' && $freeze['data']['status'] == 'AAAAAAA') {
                foreach ($payOrders as $payOrder) {
                    $payid = $payOrder['sysno'];
                    $order = $orders[$payid];

                    $paid = $payOrder['bondamount'] + $payOrder['goodsamount'] + $order['paid_money'];

                    //判断是否是退款
                    if (isset($main['is_refund']) && $main['is_refund'] == 1){
                        $paid = $order['paid_money'] - $payOrder['bondamount'] - $payOrder['goodsamount'];
                    }

                    $orderUpdate = [
                        'paid_money' => $paid,
                        'updatedat' => '=NOW()'
                    ];
                    self::getInstance()->dbh->update('order_master', $orderUpdate, 'sysno = ' . $order['sysno']);

                    $orderRow = self::getInstance()->dbh->select_row("select * from `order_master`  where sysno = " . $payOrder['order_id']);

                    //相等的话，付款步骤结束
                    $currentstep =  $orderRow['currentstep'];
                    if( ($currentstep == 1 && ( $orderRow['paid_money']  >=   round($orderRow['sum_money'] * ( 1 -   $orderRow['moreless']/100  ) ,2) ) )|| ( $orderRow['reviewstatus'] == 2 && $currentstep > 1 && $orderRow['paid_money']  ==   $orderRow['reviewmoney']) ){

                        $orderno = $orderRow['orderno'];
                        $ordertype = $orderRow['ordertype'];
                        $order_id = $orderRow['sysno'];

                        if($currentstep == 1){
                            if($ordertype == '1')
                                $status_code = 'NOR001';
                            else if($ordertype == '2')
                                $status_code = 'ROD001';
                            else
                                $status_code = 'SPC001';
                        }else{
                            if($ordertype == '1')
                                $status_code = 'NOR003';
                            else if($ordertype == '2')
                                $status_code = 'ROD003';
                            else
                                $status_code = 'SPC003';
                        }

                        $status_row = self::getInstance()->dbh->select_row("select * from order_status where orderno='$orderno' and statuscode='$status_code'");

                        if(count($status_row) == 0){
                            self::getInstance()->dbh->rollback();
                            return ReturnResult::failed(StatusCode::ORDER_NOFLOW_ERROR_CODE,StatusCode::ORDER_NOFLOW_ERROR_STRING)->toJson();
                        }

                        $resArr = array(
                            'execstatus' => 3,
                            'updated_at' =>  '=NOW()',
                            'execd_at' => '=NOW()'
                        );
                        $where = 'sysno = ' . $status_row['sysno'];

                        $res  = self::getInstance()->dbh->update('order_status', $resArr, $where);
                        if(!$res){
                            self::getInstance()->dbh->rollback();
                            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();

                        }

                        $sql = "select count(*) from order_status where order_id = '$order_id' and step = $status_row[step] and execstatus < 3";
                        $cnt = self::getInstance()->dbh->select_one($sql);

                        if($cnt ==0) {
                            $step = $status_row['step'] + 1;
                            $sql = "select count(*) from order_status where order_id = '$order_id' and step = '$step' and execstatus < 2";
                            $next_cnt = self::getInstance()->dbh->select_one($sql);
                            if ($next_cnt > 0) {
                                $mainInput['currentstep'] = $step;

                                $input = ['execstatus' => 2, 'updated_at' => '=NOW()'];
                                $where = 'order_id = ' . $order_id . " and step = " . $step;
                                $res = self::getInstance()->dbh->update('order_status', $input, $where);
                                if (!$res) {
                                    self::getInstance()->dbh->rollback();
                                    return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
                                }
                            } else {
                                $mainInput['orderstatus'] = 4; //最终完成
                            }

                            $where = 'sysno = ' . $order_id;

                            $res = self::getInstance()->dbh->update('order_master', $mainInput, $where);
                            if (!$res) {
                                self::getInstance()->dbh->rollback();
                                return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
                            }
                        }
                    }

                    $payUpdate = [
                        'status' => 2, //已支付
                        'linktime' => '=NOW()',
                        'serialno' => $clmeno,
                        'paytype' => 2
                    ];
                    self::getInstance()->dbh->update('payment_order', $payUpdate, 'sysno = ' . $payid);
                }

                $payMasterUpdate = [
                    'paystatus' => '3', //支付完成
                    'paystatustime' => '=NOW()',
                    'onlineno' => $clmeno,
                    'updatedat' => '=NOW()'
                ];
                self::getInstance()->dbh->update('payment_master', $payMasterUpdate, "paymentno = '$payno' and isdel = 0");

                self::getInstance()->dbh->commit();
                return ReturnResult::success()->toJson();


            } else {
                self::getInstance()->dbh->rollback();
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING . ':' . $freeze['data']['statusText'])->toJson();
            }
        } catch (Exception $e) {
            self::getInstance()->dbh->rollback();
            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
        }
    }


    //如果是冻结，需要进行结算
    public function settleOrder($order_id){
        if(!$order_id){
            return ReturnResult::failed(StatusCode::ORDER_NOID_ERROR_CODE,StatusCode::ORDER_NOID_ERROR_CODE)->toJson();
        }
      /*  $params =array(
            'banktype' => 'ZhongXinApi',
            'method' => 'FreezeDetail',
            'data' => array(
                'subAccNo' => '3110210029412572313',
                'startDate' =>'20180320',
                'endDate' =>'20180320',
            )
        );

        $freezes=$this->pay->paymentFunc($params);

        var_dump($freezes);
        exit();*/

        $payOrders = self::getInstance()->dbh->select("select payment_order.*, pm.pay_companyname,pm.pay_bankno,pm.receive_bankno,pm.receive_companyname  from payment_order left join payment_master pm on pm.paymentno  = payment_order.paymentno where payment_order.order_id = '$order_id' and payment_order.paytype = 1 and payment_order.status = 1 and payment_order.serialno !=''");
        if (count($payOrders) == 0) {
            //没有说明都是线下或者直接支付，直接return
            return  ReturnResult::success()->toJson();
           // return ReturnResult::failed(StatusCode::PAY_NODETAIL_ERROR_CODE, StatusCode::PAY_NODETAIL_ERROR_CODE)->toJson();
        }

        $errs = [];
        foreach( $payOrders as $payOrder){
            $serialno = $payOrder['serialno'];
            //查询冻结编号

            $params =array(
                'banktype' => 'ZhongXinApi',
                'method' => 'PayResult',
                'data' => array(
                    'list' => ['clientID' => $serialno] // 流水号

                )
            );

            try{
                $freezes=$this->pay->paymentFunc($params);
            }catch (Exception $exception){
                return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING)->toJson();
            }

            if($freezes['code']=='0000' && $freezes['data']['status']='AAAAAAA'){

                $freezeNo = $freezes['data']['list']['row']['freezeNo'];
                
                $amount = payFormat($payOrder['bondamount'] + $payOrder['goodsamount']);

                $clmeno=date("YmdHis").'_'.rand(1000,9999);
                $freeze =array(
                    'banktype' => 'ZhongXinApi',
                    'method' => 'PayAccount',
                    'data' => array(
                        'clientID' =>$clmeno,
                        'payAccNo' =>$payOrder['receive_bankno'],
                        'tranType' =>'BG',
                        'recvAccNo' =>$payOrder['receive_bankno'],
                        'recvAccNm' =>$payOrder['receive_companyname'],
                        'tranAmt' => $amount,
                        'freezeNo' =>$freezeNo,//$clmeno,
                        'ofreezeamt'=>'',
                        'memo' => $serialno .'- 解冻',
                        'tranFlag' =>1
                    )
                );

                try{
                    $unfreeze=$this->pay->paymentFunc($freeze);
                }catch (Exception $exception){
                    return ReturnResult::failed(StatusCode::PAY_BANK_ERROR_CODE, StatusCode::PAY_BANK_ERROR_STRING)->toJson();
                }

                if($unfreeze['code']=='0000' && $unfreeze['data']['status']=='AAAAAAA'){
                    $input = ['status' => 2];
                    $res = self::getInstance()->dbh->update('payment_order', $input, 'sysno = ' . $payOrder['sysno']);
                    if(!$res){
                        $errs[] = [ 'serialno' => $serialno, 'msg' =>  '数据库更新出错'];
                    }
                }else{
                    $errs[] = [ 'serialno' => $serialno, 'msg' =>  $unfreeze['data']['statusText']];
                }
            }else{
                $errs[] = [ 'serialno' => $serialno, 'msg' =>  $freezes['data']['statusText']];
            }
        }

        if(count($errs) > 0){
            return ReturnResult::failed(StatusCode::ORDER_UNFREEZE_ERROR_CODE, json_encode($errs))->toJson();
        }else
            return  ReturnResult::success()->toJson();
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
        $sql = "select bankaccountno from td_companies_account WHERE companies_id = $companyNo and status = 1";

        if (Yaf_Registry:: get("guoyie_db") instanceof MySQL) {
            $this->dbh = Yaf_Registry:: get("guoyie_db");
            $bankaccountno = $this->dbh->select_one($sql);
            if ($bankaccountno){
                return $this->_getZhongxinAmount($bankaccountno);
            }else{
                throw new Yaf_Exception(StatusCode::CLIENT_DATA_NOT_EXISTS_STRING,StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
            }
        } else {
            throw new Yaf_Exception("guoyie_db配置不对",StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
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
//        $params = array('total_amount'=>'正在查询','frozen_amount'=>'正在查询','active_amount'=>'正在查询');


        if(!$bankaccountno){
            $params = array('total_amount'=>'暂时无法查询','frozen_amount'=>'暂时无法查询','active_amount'=>'暂时无法查询');
//            return $params;
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
        $main = self::getInstance()->dbh->select_row("select * from payment_master where paymentno = '$payno' and isdel = 0");
        if (count($main) == 0) {
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_NOEXIST_ERROR_CODE, StatusCode::PAY_NOEXIST_ERROR_CODE)->toJson();
        }


        if ($main['paystatus'] == 3) {
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_HASPAID_ERROR_CODE, StatusCode::PAY_HASPAID_ERROR_CODE)->toJson();
        }

        $payOrders = self::getInstance()->dbh->select("select * from payment_order where paymentno = '$payno' and status != 4");

        if (count($payOrders) == 0) {
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::PAY_NODETAIL_ERROR_CODE, StatusCode::PAY_NODETAIL_ERROR_CODE)->toJson();
        }

        self::getInstance()->dbh->begin();
        try {
            $orders = [];

            $payamount = 0;


            foreach ($payOrders as $payOrder) {
                $payid = $payOrder['sysno'];
                $order = self::getInstance()->dbh->select_row("select * from order_master where sysno = '$payOrder[order_id]' for update");
                //如果确认复核后，则应付金额不是sum_money 是 reviewmoney
                if($order['reviewstatus'] == 2){
                    $orderMoney = $order['reviewmoney'] - $order['paid_money']; //未付金额
                }else{
                    $orderMoney = $order['sum_money'] - $order['paid_money']; //未付金额
                }
                $payOrderMoney = $payOrder['bondamount'] + $payOrder['goodsamount'];

                /*if (($orderMoney-$payOrderMoney) < 0 && round($orderMoney,2) != round($payOrderMoney,2)) {
                    self::getInstance()->dbh->rollback();
                    Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
                    return ReturnResult::failed(StatusCode::PAY_OVERLIMIT_ERROR_CODE, StatusCode::PAY_OVERLIMIT_ERROR_STRING . ':' . $order['orderno'])->toJson();
                }*/

                $orders[$payid] = $order;
                $payamount += $payOrder['bondamount'] + $payOrder['goodsamount'];
            }

            if (round($payamount,2) != round($main['bondamount'] + $main['goodsamount'],2)) {
                self::getInstance()->dbh->rollback();
                Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
                return ReturnResult::failed(StatusCode::PAY_NOMATCH_ERROR_CODE, StatusCode::PAY_NOMATCH_ERROR_STRING)->toJson();
            }
            $clmeno = date("YmdHis") . '_' . rand(1000, 9999); //流水号与冻结号

            foreach ($payOrders as $payOrder) {
                $payid = $payOrder['sysno'];

                $payUpdate = [
                    'status' => 3, //3 已提交待审核
                    'linktime' => '=NOW()',
//                    'serialno' => $clmeno,
                    'paytype' => 0  //线下支付
                ];
                self::getInstance()->dbh->update('payment_order', $payUpdate, 'sysno = ' . $payid);
            }

            $payMasterUpdate = [
                'paystatus' => '2', //支付完成待审核
                'paystatustime' => '=NOW()',
                'updatedat' => '=NOW()'
            ];
            self::getInstance()->dbh->update('payment_master', $payMasterUpdate, "paymentno = '$payno' and isdel = 0");
            self::getInstance()->dbh->commit();
            return ReturnResult::success()->toJson();

        } catch (Exception $e) {
            self::getInstance()->dbh->rollback();
            Payment_FilesModel::getInstance()->deletePaymentFiles($payno);
            return ReturnResult::failed(StatusCode::SERVER_MYSQL_ERROR_CODE, StatusCode::SERVER_MYSQL_ERROR_STRING)->toJson();
        }
    }
}