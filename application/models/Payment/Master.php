<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/6 11:12
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 收付款单-主表
 */
class Payment_MasterModel
{
    /**
     * @var string  默认的表名
     */
    public static $tableName = 'payment_master';
    /**
     * @var MySQL
     */
    public $dbh = null;

    //静态变量保存全局实例
    /**
     * @var null
     */
    private static $_instance = null;

    //私有构造函数，防止外界实例化对象
    private function __construct()
    {
    }

    //私有克隆函数，防止外办克隆对象
    private function __clone()
    {
    }

    /**
     * @return null|Payment_MasterModel
     * @throws Yaf_Exception
     * 静态方法，单例统一访问入口
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance) || isset (self::$_instance)) {
            self::$_instance = new self ();
            if (Yaf_Registry:: get("db") instanceof MySQL) {
                self::$_instance->dbh = Yaf_Registry:: get("db");
            } else {
                throw new Yaf_Exception("db配置不对");
            }
        }

        return self::$_instance;
    }

    /**
     * @param array $params 收付款单参数
     * @return mixed
     */
    public function addPaymentMaster($params)
    {
        $params['paymenttime'] = '=NOW()';
        $params['paystatustime'] = '=NOW()';
        $params['createdat'] = '=NOW()';
        $params['updatedat'] = '=NOW()';
        $params['additivetime'] = '=NOW()';
        return $this->dbh->insert(self::$tableName, $params);
    }

    /**
     * @param $paymentno
     * @param $data
     * @return bool
     */
    public function updatePaymentMaster($paymentno, $data)
    {
        $data['updatedat'] = '=NOW()';
        return $this->dbh->update(self::$tableName, $data, "paymentno = '" . $paymentno."'");
    }

    /**
     * @param array $masterParams  收付款单参数
     * @param array $filesParams   收付款单-附件表参数
     * @param array $orderParams   收付款单-付款单关联订单表参数
     * @param array $logParams     收付款单操作日志参数
     * @return string
     * @throws Yaf_Exception
     * 创建生成收付款单
     */
    public function addMasterAndFilesAndOrderAndLog($masterParams,$filesParams,$orderParams,$logParams){
        $this->dbh->begin();
        try{
            $this->addPaymentMaster($masterParams);
            $paymentNo = isset($masterParams['paymentno'])?$masterParams['paymentno']:null;
            if ($paymentNo == null){
                throw new Yaf_Exception("没有找到相对应生成的收付款单编号");
            }

            if ($filesParams != null){
                $filesParams['paymentno'] = $paymentNo;
                Payment_FilesModel::getInstance()->addPaymentListFiles($filesParams);
            }
            if ($orderParams != null){
                $orderParams['paymentno'] = $paymentNo;
                Payment_OrderModel::getInstance()->addPaymentListOrder($orderParams);
            }
            $logParams['paymentno'] = $paymentNo;
           // Payment_LogModel::getInstance()->addPaymentLog($logParams);
            $this->dbh->commit();
            return $paymentNo;
        }catch (Yaf_Exception $yaf_Exception){
            $this->dbh->rollback();
            throw new Yaf_Exception('添加出错,'.$yaf_Exception->getMessage());
        }
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $filesParams   收付款单-附件表参数
     * @param array $orderParams   收付款单-付款单关联订单表参数
     * @param array $logParams     收付款单操作日志参数
     * @return mixed
     * @throws Yaf_Exception
     */
    public function updatedMasterAndFilesAndOrderAndLog($paymentNo,$masterParams,$filesParams,$orderParams,$logParams){

        $this->dbh->begin();
        try{
            if ($paymentNo == null){
                throw new Yaf_Exception("没有找到相对应收付款单编号");
            }
            $logParams['paymentno'] = $paymentNo;
            $this->updatePaymentMaster($paymentNo,$masterParams);
            Payment_FilesModel::getInstance()->updatePaymentFiles($paymentNo,$filesParams);
            Payment_OrderModel::getInstance()->updatePaymentOrder($paymentNo,$orderParams);
            Payment_LogModel::getInstance()->addPaymentLog($logParams);
            $this->dbh->commit();
            return $paymentNo;
        }catch (Yaf_Exception $yaf_Exception){
            $this->dbh->rollback();
            throw new Yaf_Exception('更新出错,'.$yaf_Exception->getMessage());
        }
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $logParams     收付款单操作日志参数
     * @return mixed
     * @throws Yaf_Exception
     */
    public function updatedMasterAndLog($paymentNo,$masterParams,$logParams){

        $this->dbh->begin();
        try{
            if ($paymentNo == null){
                throw new Yaf_Exception("没有找到相对应收付款单编号");
            }

            //判断是否可以删除
            if (isset($masterParams['isdel']) && $masterParams['isdel'] == 1){
                $paymentMaster = $this->dbh->select_row("select * from ".self::$tableName." where paymentno='$paymentNo' and isdel = 0");
                if ($paymentMaster == null){
                    throw new Yaf_Exception(StatusCode::CLIENT_DATA_NOT_EXISTS_STRING,StatusCode::CLIENT_DATA_NOT_EXISTS_CODE);
                }
                if ($paymentMaster['paystatus'] != 1){
                    throw new Yaf_Exception("该收付款单应经过了确认环节，所以不能删除",StatusCode::CLIENT_ERROR_CODE);
                }
            }

            $this->updatePaymentMaster($paymentNo,$masterParams);
            if ($logParams != null){
                $logParams['paymentno'] = $paymentNo;
                Payment_LogModel::getInstance()->addPaymentLog($logParams);
            }
            $this->dbh->commit();
            return $paymentNo;
        }catch (Yaf_Exception $yaf_Exception){
            $this->dbh->rollback();
            throw new Yaf_Exception('更新出错,'.$yaf_Exception->getMessage(),StatusCode::CLIENT_ERROR_CODE);
        }
    }

    /**
     * @param string $paymentNo 收付款单编号
     * @param string $companyName    收付款单企业人
     * @return mixed
     * @throws Yaf_Exception
     * 查询一个收付款单
     */
    public function findMaster($paymentNo,$companyName){
        if ($paymentNo == null || $companyName == null){
            throw new Yaf_Exception('收付款单号不能为空！');
        }
        //判断这个收付款单是否属于这个企业所有
        $sql="SELECT * from ".self::$tableName." where paymentno = '$paymentNo' and (pay_companyname = '$companyName' or receive_companyname = '$companyName')";
        $info = $this->dbh->select_one($sql);
        if($info == null){
            throw new Yaf_Exception('该收付款单不属于您！');
        }

        $sql = "SELECT * from ".self::$tableName." where paymentno = '$paymentNo' and isdel = 0";
        $masterInfo = $this->dbh->select_row($sql);
        $sql = "
            SELECT payment_order.*
            FROM  " . Payment_OrderModel::$tableName . " as payment_order
            WHERE payment_order.paymentno = '" . $paymentNo. " '";
        $masterInfo['orderList'] = $this->dbh->select($sql);
        $sql = "SELECT * from ".Payment_FilesModel::$tableName." where paymentno = '$paymentNo'";
        $masterInfo['filesList'] = $this->dbh->select($sql);
        return $masterInfo;

    }

    /**
     * @param $search
     * @return mixed
     * 收付款单列表
     */
    public function getListPayment($search)
    {
        $like = null;
        $paymentLike = null;
        $result['count'] = ''; //个个状态的数量
        $paymentno = isset($search['paymentno']) ? $search['paymentno'] : null;  //收付款单编号
        if ($paymentno != null) {
            $like .= " and paymentno = '" . $paymentno . "'";
            $paymentLike .= " and payment_master.paymentno = '" . $paymentno . "'";
        }
        $pay_companyno = isset($search['pay_companyno']) ? $search['pay_companyno'] : null;  //付款方公司编号
        if ($pay_companyno != null) {
            $like .= " and pay_companyno = '" . $pay_companyno . "'";
            $paymentLike .= " and payment_master.pay_companyno = '" . $pay_companyno . "'";
//            $sql = "select paystatus,count(*)  as count from " . static::$tableName . " where isdel = 0 and pay_companyno ='".$pay_companyno."' GROUP BY paystatus";
//            $result['count'] = $this->dbh->select($sql);
        }
        $pay_companyname = isset($search['pay_companyname']) ? $search['pay_companyname'] : null;  //付款方公司名称
        if ($pay_companyname != null) {
            $like .= " and pay_companyname = '" . $pay_companyname . "'";
            $paymentLike .= " and payment_master.pay_companyname = '" . $pay_companyname . "'";
        }
        $receive_companyno = isset($search['receive_companyno']) ? $search['receive_companyno'] : null;  //收款方公司编号
        if ($receive_companyno != null) {
            $like .= " and receive_companyno = '" . $receive_companyno . "'";
            $paymentLike .= " and payment_master.receive_companyno = '" . $receive_companyno . "'";
//            $sql = "select paystatus,count(*)  as count from " . static::$tableName . " where isdel = 0 and receive_companyno ='".$receive_companyno."' GROUP BY paystatus";
//            $result['count'] = $this->dbh->select($sql);
        }
        $receive_companyname = isset($search['receive_companyname']) ? $search['receive_companyname'] : null;  //收款方公司名
        if ($receive_companyname != null) {
            $like .= " and receive_companyname = '" . $receive_companyname . "'";
            $paymentLike .= " and payment_master.receive_companyname = '" . $receive_companyname . "'";
        }

        $status = isset($search['status']) ? $search['status'] : null;  //状态：1启用2停用
        if ($status != null) {
            $like .= " and status = " . $status ;
            $paymentLike .= " and payment_master.status = " . $status ;
        }
        $start_time = isset($search['start_time']) ? $search['start_time'] : null;  //开始时间
        $end_time = isset($search['end_time']) ? $search['end_time'] : null;  //结束时间
        if($start_time != null && $end_time != null){
            if ($pay_companyno != null){  //付款管理
                $like .= " and validatortime >= '" . $start_time." 00:00:00' and validatortime <= '" . $end_time." 23:59:59' " ;
                $paymentLike .= " and payment_master.validatortime >= '" . $start_time." 00:00:00' and payment_master.validatortime <= '" . $end_time." 23:59:59' " ;
            }
            if ($receive_companyno != null){ //收款管理
                $like .= " and auditortime >= '" . $start_time." 00:00:00' and auditortime <= '" . $end_time." 23:59:59'" ;
                $paymentLike .= " and payment_master.auditortime >= '" . $start_time." 00:00:00' and payment_master.auditortime <= '" . $end_time." 23:59:59' " ;
            }
        }

        $sql = "select paystatus,count(*)  as count from " . static::$tableName . " where isdel = 0 and source = 0".$like." GROUP BY paystatus";
        $result['count'] = $this->dbh->select($sql);

//        //判断收付款驳回的显示（未支付驳回的话，收款方的驳回信息不应该出现）
//        if ($pay_companyno != null) {
//            $sql = "select paystatus,count(*)  as count from " . static::$tableName . " where isdel = 0 and source = 0".$like." GROUP BY paystatus";
//            $result['count'] = $this->dbh->select($sql);
//        }
//        if ($receive_companyno != null) {
//            $sql = "select paystatus,count(*)  as count from " . static::$tableName . " where isdel = 0 and source = 0 and validator != '' ".$like." GROUP BY paystatus";
//            $result['count'] = $this->dbh->select($sql);
//        }



        $paystatus = isset($search['paystatus']) ? $search['paystatus'] : null;  //付款单状态：1新建2确认中3已付款4已驳回

        if ($paystatus != null) {
            $like .= " and paystatus in (" . $paystatus.")" ;
            $paymentLike .= " and payment_master.paystatus in (" . $paystatus.")" ;
//            //判断收付款驳回的显示（未支付驳回的话，收款方的驳回信息不应该出现）
//            if ($receive_companyno != null){
//                if ($paystatus == 4 || $paystatus == '2,3,4'){
//                    $like .= "and validator != '' and paystatus in (" . $paystatus.")" ;
//                    $paymentLike .= "and payment_master.validator != '' and payment_master.paystatus in (" . $paystatus.")" ;
//                }else{
//                    $like .= " and paystatus in (" . $paystatus.")" ;
//                    $paymentLike .= " and payment_master.paystatus in (" . $paystatus.")" ;
//                }
//            }else{
//                $like .= " and paystatus in (" . $paystatus.")" ;
//                $paymentLike .= " and payment_master.paystatus in (" . $paystatus.")" ;
//            }
        }



        //获取总的记录数
        $sql = " SELECT COUNT(*) FROM " . static::$tableName . " where isdel = 0 and source = 0 " . $like;

        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['total'] = $result['totalRow'];
        $result['rows'] = [];
        if ($result['totalRow']) {
            //设置当前页和pageSize
            $pageCurrent = isset($search['page']) ? $search['page'] : 1;
            $pageSize = isset($search['pageSize']) ? $search['pageSize'] : 10;
            //获取总的分页数
            $result['totalPage'] = ceil($result['totalRow'] / $pageSize);

            $this->dbh->set_page_num($pageCurrent);
            $this->dbh->set_page_rows($pageSize);

            $sql = "
            SELECT payment_master.paymentno,payment_master.receive_companyname,payment_master.goodsamount,payment_master.validatortime,payment_master.paystatus,payment_master.pay_companyname,payment_master.auditortime,payment_master.is_refund,
            payment_files.filetype,payment_files.filename,GROUP_CONCAT(payment_order.orderno) as ordernos,payment_order.bondamount,payment_order.goodsamount as paymentordergoodsamount,payment_order.linktime,payment_order.serialno
            FROM  " . self::$tableName . " as payment_master 
            LEFT JOIN " . Payment_FilesModel::$tableName . " as payment_files ON payment_master.paymentno = payment_files.paymentno
            LEFT JOIN " . Payment_OrderModel::$tableName . " as payment_order ON payment_master.paymentno = payment_order.paymentno
            WHERE payment_master.isdel = 0 and payment_master.source = 0 " . $paymentLike ."  group by payment_master.sysno order by payment_master.updatedat desc";

            $result['rows'] = $this->dbh->select_page($sql);
        }
        //返回结果
        return $result;
    }

    /**
     * @param string $paymentNo 收付款单编号
     * @param $masterParams
     * @param $logParams
     * @return mixed
     * @throws Yaf_Exception
     * 驳回付款单
     */
    public function rejectPayment($paymentNo,$masterParams,$logParams){
        $this->dbh->begin();
        try{
            if ($paymentNo == null){
                throw new Yaf_Exception("没有找到相对应收付款单编号");
            }

            $paystatus = $this->dbh->select_one("select paystatus from ".self::$tableName." where isdel = 0 and paymentno = '$paymentNo'");
            if ($paystatus){
                if ($paystatus == 2 && $masterParams['type'] == 2){
                    throw new Yaf_Exception('该付款单已经被确认，所以不能驳回！');
                }
                if ($paystatus == 3 && $masterParams['type'] == 3){
                    throw new Yaf_Exception('该付款单已经被对方确认了收款，所以不能驳回！');
                }
                if ($paystatus == 4){
                    throw new Yaf_Exception('该付款单已经被驳回，所以不能驳回！');
                }
            }
            unset($masterParams['type']);
            $this->updatePaymentMaster($paymentNo,$masterParams);
            if ($logParams != null){
                $logParams['paymentno'] = $paymentNo;
                Payment_LogModel::getInstance()->addPaymentLog($logParams);
            }
            Payment_OrderModel::getInstance()->updatePaymentOrder($paymentNo,['status'=>4]);
            $this->dbh->commit();
            return $paymentNo;
        }catch (Yaf_Exception $yaf_Exception){
            $this->dbh->rollback();
            throw new Yaf_Exception($yaf_Exception->getMessage());
        }
    }

    /**
     * @param string $paymentNo 收付款单编号
     * @param $masterParams
     * @param $logParams
     * @return mixed
     * @throws Yaf_Exception
     * 确认支付
     */
    public function confirmPayment($paymentNo,$masterParams,$logParams){
        $this->dbh->begin();
        try
        {
            if ($paymentNo == null){
                throw new Yaf_Exception("没有找到相对应收付款单编号");
            }

            $paymentInfo = $this->dbh->select_row("select is_refund,paystatus from ".self::$tableName." where isdel = 0 and paymentno = '$paymentNo'");

            if ($paymentInfo == null){
                throw new Yaf_Exception("没有找到相对应收付款单编号");
            }

            if ($paymentInfo['paystatus'] == 4){
                throw new Yaf_Exception('该付款单已经被对方驳回，所以不能确认支付');
            }

            $this->updatePaymentMaster($paymentNo,$masterParams);
            if ($logParams != null){
                $logParams['paymentno'] = $paymentNo;
                Payment_LogModel::getInstance()->addPaymentLog($logParams);
            }
            Payment_OrderModel::getInstance()->updatePaymentOrder($paymentNo,['status'=>2]);

            $payOrders = $this->dbh->select("select * from payment_order where paymentno = '$paymentNo' and status =2 for update");
            foreach ($payOrders as $payOrder) {
                $paid = $payOrder['bondamount'] + $payOrder['goodsamount'] ;
                //判断是否是退款
                if (isset($paymentInfo['is_refund']) && $paymentInfo['is_refund'] == 1){
                    $sql = "UPDATE LOW_PRIORITY `order_master` SET `paid_money`= paid_money - $paid ,updatedat=NOW() WHERE sysno = ".$payOrder['order_id'];
                }else{
                    $sql = "UPDATE LOW_PRIORITY `order_master` SET `paid_money`= paid_money + $paid ,updatedat=NOW() WHERE sysno = ".$payOrder['order_id'];
                }
                $this->dbh->exe($sql);
                $orderRow = $this->dbh->select_row("select * from `order_master`  where sysno = " . $payOrder['order_id']);
                if($orderRow['orderstatus'] != 3){
                    throw new Yaf_Exception("此单据号：".$orderRow['orderno']."不是进行中状态，不能进行确认操作");
                }
                if($orderRow['reviewstatus'] == 2){
                    if($orderRow['paid_money'] > $orderRow['reviewmoney']){
                        throw new Yaf_Exception("此单据号：".$orderRow['orderno']."的实付金额大于应付金额，更新业务流失败");
                    }
                }else{
                    //付款与交割，目前改成可以多付
                    /*if($orderRow['paid_money'] > $orderRow['sum_money']){
                        $this->dbh->rollback();
                        throw new Yaf_Exception("此单据号：".$orderRow['orderno']."的实付金额大于应付金额，更新业务流失败");
                    }*/
                }

                //相等的话，付款步骤结束
                $currentstep =  $orderRow['currentstep'];
                if( ($currentstep == 1 &&  ( $orderRow['paid_money']  >=   round($orderRow['sum_money'] * ( 1 -   $orderRow['moreless']/100  ) ,2) ) )|| ( $orderRow['reviewstatus'] == 2 && $currentstep > 1 && $orderRow['paid_money']  ==   $orderRow['reviewmoney']) ){
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
                    $status_row = $this->dbh->select_row("select * from order_status where orderno='$orderno' and statuscode='$status_code'");
                    if(count($status_row) == 0){
                        throw new Yaf_Exception(StatusCode::ORDER_NOFLOW_ERROR_STRING);
                    }
                    $resArr = array(
                        'execstatus' => 3,
                        'updated_at' =>  '=NOW()',
                        'execd_at' => '=NOW()'
                    );
                    $where = 'sysno = ' . $status_row['sysno'];

                    $res  = $this->dbh->update('order_status', $resArr, $where);
                    if(!$res){
                        throw new Yaf_Exception(StatusCode::SERVER_MYSQL_ERROR_STRING."，更新业务流失败");

                    }

                    $sql = "select count(*) from order_status where order_id = '$order_id' and step = $status_row[step] and execstatus < 3";
                    $cnt = $this->dbh->select_one($sql);

                    if($cnt ==0) {
                        $step = $status_row['step'] + 1;
                        $sql = "select count(*) from order_status where order_id = '$order_id' and step = '$step' and execstatus < 2";
                        $next_cnt = $this->dbh->select_one($sql);
                        if ($next_cnt > 0) {
                            $mainInput['currentstep'] = $step;

                            $input = ['execstatus' => 2, 'updated_at' => '=NOW()'];
                            $where = 'order_id = ' . $order_id . " and step = " . $step;
                            $res = $this->dbh->update('order_status', $input, $where);
                            if (!$res) {
                                throw new Yaf_Exception(StatusCode::SERVER_MYSQL_ERROR_STRING."，更新业务流失败");
                            }
                        } else {
                            $mainInput['orderstatus'] = 4; //最终完成
                        }

                        $where = 'sysno = ' . $order_id;

                        $res = $this->dbh->update('order_master', $mainInput, $where);
                        if (!$res) {
                            throw new Yaf_Exception(StatusCode::SERVER_MYSQL_ERROR_STRING."，更新业务流失败");
                        }
                    }
                }
            }
            $this->dbh->commit();
            return $paymentNo;
        }catch (Yaf_Exception $yaf_Exception){
            $this->dbh->rollback();
            throw new Yaf_Exception($yaf_Exception->getMessage());
        }
    }

    /**
     * @param string $paymentNo 收付款单编号
     * @param bool $isOther 是否获得当前收付款单下的所有订单和附件文件
     * @return mixed
     * @throws Yaf_Exception
     * 获得该收付款单的所有信息
     */
    public function getPaymentInfo($paymentNo,$isOther = true){
        if ($paymentNo == null){
            throw  new Yaf_Exception(StatusCode::CLIENT_EMPTY_PARAMETER_STRING,StatusCode::CLIENT_EMPTY_PARAMETER_CODE);
        }
        $sql = "SELECT * from ".self::$tableName." where paymentno = '$paymentNo' and isdel = 0";
        $masterInfo = $this->dbh->select_row($sql);
        if ($isOther){
            $sql = "SELECT * from ".Payment_OrderModel::$tableName." where paymentno = '$paymentNo'";
            $masterInfo['paymentOrderList'] = $this->dbh->select($sql);
            $sql = "SELECT * from ".Payment_FilesModel::$tableName." where paymentno = '$paymentNo'";
            $masterInfo['paymentFilesList'] = $this->dbh->select($sql);
        }else{
            $masterInfo['paymentOrderList'] = null;
            $masterInfo['paymentFilesList'] = null;
        }

        return $masterInfo;
    }

}