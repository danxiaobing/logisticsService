<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/6 11:12
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 收付款单-付款单关联订单表
 */
class Payment_OrderModel
{
    /**
     * @var string  默认的表名
     */
    public static $tableName = 'payment_order';
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
     * @return null|Payment_OrderModel
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
     * @param array $params 收付款单-付款单关联订单表
     * @return mixed
     */
    public function addPaymentOrder($params)
    {
        $params['linktime'] = '=NOW()';
        return $this->dbh->insert(self::$tableName, $params);
    }

    /**
     * @param array $params  订单列表数据
     *  例子：$params['paymentno'=>111,'orderList'=>[[],[],[]]]
     * @return bool
     * @throws Yaf_Exception
     * 收付款单-付款单关联订单列表表
     */
    public function addPaymentListOrder($params){
        $paymentno = isset($params['paymentno'])?$params['paymentno']:null;
        if($paymentno == null){
            throw new Yaf_Exception('添加收付款单文件时，收付款单号不能为空');
        }
        $orderList = isset($params['orderList'])?$params['orderList']:null;
        if($orderList == null || !is_array($orderList)){
            throw new Yaf_Exception('添加收付款单文件时，没有找到订单列表');
        }

        foreach ($orderList as $key=>$order){
            $order['paymentno'] = $paymentno;
            try{
                $this->addPaymentOrder($order);
            }catch (Exception $exception){
                throw new Yaf_Exception($exception->getMessage());
            }
        }
        return true;
    }

    /**
     * @param $paymentno
     * @param $data
     * @return bool
     * 更新收付款单-付款单关联订单表
     */
    public function updatePaymentOrder($paymentno, $data)
    {
//        $data['linktime'] = '=NOW()';
        return $this->dbh->update(self::$tableName, $data, "paymentno = '" . $paymentno."'");
    }

    /**
     * 根据订单号查询订单的已支付运费
     * @params string $orderno  订单编号
     * @return int
     */
    public function getOrderPayFreightamount($orderno){
        $sql  = "SELECT SUM(freightamount) as sum_freight FROM `payment_order` WHERE orderno='".$orderno."' AND status in (1,2)";
        $data = $this->dbh->select_row($sql);
        return $data;
    }

    /**
     * @param string $orderNo 订单编号
     * @param bool $isOther 是否获得当前收付款单下的所有订单和附件文件
     * @return mixed
     * @throws Yaf_Exception
     * 获得订单的所有信息
     */
    public function getOrderPayList($orderNo,$isOther = true){
        if ($orderNo == null){
            throw  new Yaf_Exception(StatusCode::CLIENT_EMPTY_PARAMETER_STRING."，订单的编号不能为空！",StatusCode::CLIENT_EMPTY_PARAMETER_CODE);
        }

        //判断订单号是否真实存在
        $sql = "select * from order_master where orderno='".$orderNo."' and isdel = 0";
        $orderInfo = $this->dbh->select_row($sql);
        if ($orderInfo == null){
            throw  new Yaf_Exception(StatusCode::CLIENT_ILLEGAL_PARAMETER_STRING."，订单的编号错误！",StatusCode::CLIENT_ILLEGAL_PARAMETER_CODE);
        }

        //获得所有的payment_order的数据
        $sql = "select * from ".self::$tableName." where orderno = '".$orderNo."'";
        $paymentOrderList = $this->dbh->select($sql);
        if ($paymentOrderList == null){
            $result['orderInfo'] = $orderInfo;
            $result['paymentOrderList'] = [];
            return $result;
        }



        foreach ($paymentOrderList as $key=>$paymentOrder){
            $paymentOrderList[$key]['paymentList'] = Payment_MasterModel::getInstance()->getPaymentInfo($paymentOrder['paymentno'],$isOther);
            if (!isset($paymentOrderList[$key]['paymentList']['isdel']) || $paymentOrderList[$key]['paymentList']['isdel'] == 1){
                $paymentOrderList[$key]['paymentList'] = null;
                unset($paymentOrderList[$key]);
            }
        }

        $result['orderInfo'] = $orderInfo;
        $result['paymentOrderList'] = $paymentOrderList;
        return $result;
    }

}