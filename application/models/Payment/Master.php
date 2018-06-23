<?php

/**
 * 收付款单-付款单关联订单表
 */
class Payment_MasterModel
{
    public $dbh = null;

    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null)
    {
        $this->dbh = $dbh;
    }

    /**
     * 获得订单的所有信息
     */
    public function getList($params){
        $filter = array();

        $where = ' g.isdel = 0 ';


        if (isset($params['carrier_id']) && !empty($params['carrier_id'])) {
            $filter[] = " g.`receive_companyno` =".$params['carrier_id'];
        }

        if (isset($params['paystatus']) && !empty($params['paystatus'])) {
            $filter[] = " g.`paystatus` = '{$params['paystatus']}'";
        }

        if (isset($params['pay_companyname']) && !empty($params['pay_companyname'])) {
            $filter[] = " g.`pay_companyname` = '%{$params['pay_companyname']}%'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " unix_timestamp(g.`created_at`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " unix_timestamp(g.`created_at`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM payment_master g  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT g.*
                FROM payment_master g
                WHERE  {$where}
                ORDER BY g.id DESC";
        $result['list']  = $this->dbh->select_page($sql);

        return $result;
    }


    /**
     * @param string $paymentNo 收付款单编号
     * @param bool $isOther 是否获得当前收付款单下的所有订单和附件文件
     * @return mixed
     * @throws Yaf_Exception
     * @author daley
     * @data 20180623
     * 获得该收付款单的所有信息
     */
    public function getPaymentInfo($paymentNo,$isOther = true){
        if ($paymentNo == null){
            throw  new Yaf_Exception(StatusCode::CLIENT_EMPTY_PARAMETER_STRING,StatusCode::CLIENT_EMPTY_PARAMETER_CODE);
        }
        $sql = "SELECT * from payment_master where paymentno = '$paymentNo' and isdel = 0";
        $masterInfo = $this->dbh->select_row($sql);
        if ($isOther){
            $sql = "SELECT * from payment_order where paymentno = '$paymentNo'";
            $masterInfo['paymentOrderList'] = $this->dbh->select($sql);
            $sql = "SELECT * from payment_files where paymentno = '$paymentNo'";
            $masterInfo['paymentFilesList'] = $this->dbh->select($sql);
        }else{
            $masterInfo['paymentOrderList'] = null;
            $masterInfo['paymentFilesList'] = null;
        }

        return $masterInfo;
    }

    /**
     * @param string $paymentNo    收付款单编号
     * @param array $masterParams  收付款单参数
     * @param array $logParams     收付款单操作日志参数
     * @return mixed
     * @throws Yaf_Exception
     * @author daley
     * @data 20180623
     */
    public function updatedMasterAndLog($paymentNo,$masterParams,$logParams){

        $this->dbh->begin();
        try{
            if ($paymentNo == null){
                throw new Yaf_Exception("没有找到相对应收付款单编号");
            }

            //判断是否可以删除
            if (isset($masterParams['isdel']) && $masterParams['isdel'] == 1){
                $paymentMaster = $this->dbh->select_row("select * from payment_master where paymentno='$paymentNo' and isdel = 0");
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
     * @param $paymentno
     * @param $data
     * @return bool
     * @author daley
     * @data 20180623
     */
    public function updatePaymentMaster($paymentno, $data)
    {
        $data['updatedat'] = '=NOW()';
        return $this->dbh->update('payment_master', $data, "paymentno = '" . $paymentno."'");
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
        $sql="SELECT * from payment_master where paymentno = '$paymentNo' and (pay_companyname = '$companyName' or receive_companyname = '$companyName')";
        $info = $this->dbh->select_one($sql);
        if($info == null){
            throw new Yaf_Exception('该收付款单不属于您！');
        }

        $sql = "SELECT * from payment_master where paymentno = '$paymentNo' and isdel = 0";
        $masterInfo = $this->dbh->select_row($sql);
        $sql = " SELECT payment_order.*,order_master.fact_freight
            FROM  payment_order as payment_order
            LEFT JOIN gl_order as order_master ON payment_order.order_id = order_master.id
            WHERE payment_order.paymentno = '" . $paymentNo. " '";
        $masterInfo['orderList'] = $this->dbh->select($sql);
        $sql = "SELECT * from payment_files where paymentno = '$paymentNo'";
        $masterInfo['filesList'] = $this->dbh->select($sql);
        return $masterInfo;

    }


    //新增
    public function affirm($data)
    {
        // print_r($data);die;
        // echo 1;die;
        return $this->dbh->insert('payment_master',$data);
    }

    //详情 
    public function getInfo($id){

        //查询询价单信息
        $sql = "SELECT * FROM payment_master i WHERE i.isdel = 0 AND i.id=".$id." ORDER BY id DESC";
        $result = $this->dbh->select_row($sql);
        return $result;
    }


    /**
     * 修改付款单
     * @param string $paymentno
     * @param int $paystatus
     * @return bool
     */
    public function updatePaymentStatus($paymentno,$paystatus){
        if ($paymentno == null || $paystatus == null){
            throw new Yaf_Exception('收付款单号信息不能为空！');
        }

        $orderSql = "SELECT order_id from payment_order where paymentno = '$paymentno'";
        $info = $this->dbh->select_one($orderSql);

        if($info == null){
            throw new Yaf_Exception('该收付款单不存在！');
        }

        #事务
        $this->dbh->begin();
        try{

            #修改付款单
            $masterResult =  $this->dbh->update('payment_master',$paystatus,"paymentno = '" . $paymentno."'");
            if(!$masterResult){
                $this->dbh->rollback();
                throw new Yaf_Exception('修改失败！');
            }

            #修改结算单
            $orderResult = $this->dbh->update('payment_order',['status'=>1],"paymentno = '" . $paymentno."'");
            if(!$orderResult){
                $this->dbh->rollback();
                throw new Yaf_Exception('修改失败！');
            }

            #修改托运单
            $consignResult = $this->dbh->update('gl_order',['status'=>5],"id = '" . $info['order_id']."'");
            if($consignResult){
                $this->dbh->commit();
                return true;
            }else{
                $this->dbh->rollback();
                throw new Yaf_Exception('修改失败！');
            }

        }catch (Exception $exception){
            throw new Yaf_Exception('修改失败！');
        }
    }
}