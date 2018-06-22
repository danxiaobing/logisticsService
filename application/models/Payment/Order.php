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
        $param['dealno'] = $this->get_random($len=4);
        $param['created_at'] = '=NOW()';
        //事务
        $this->dbh->begin();
        try{
            $res = $this->dbh->insert('payment_order',$params);
            if(!$res){
             $this->dbh->rollback();
             return array('code'=>'300','msg'=>'生成结算单失败'); 
            }

            //更新托运单状态
            $result = $this->dbh->update('gl_order',array('status'=>'9'),'id='.intval($params['order_id']));
            $this->dbh->commit();
            return array('code'=>'200','msg'=>'生成结算单成功');
            
        }catch(Exception $e){
             $this->dbh->rollback();
            return array('code'=>'300','msg'=>'生成结算单失败');           
        }
    }

    private static function get_random($len=3){  
          //range 是将10到99列成一个数组   
          $numbers = range (10,99);  
          //shuffle 将数组顺序随即打乱   
          shuffle ($numbers);   
          //取值起始位置随机  
          $start = mt_rand(1,10);  
          //取从指定定位置开始的若干数  
          $result = array_slice($numbers,$start,$len);   
          $random = "";  
          for ($i=0;$i<$len;$i++){   
             $random = $random.$result[$i];  
           }   
           $str = date('mdHi');
          return $str.$random;  
     }




     //list结算单
     public function getpaylist($params){
        //计算总数
        $sql = 'SELECT count(1) FROM payment_order WHERE c_id='.intval($params['c_id']);
        $data = $this->dbh->select_one($sql);

        $result['totalRow'] = $data ? $data:[];

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

        $sql = 'SELECT gy.`id`,gy.`c_id`,gy.`cargo_id`,gy.`order_id`,gy.`goods_id`,gy.`paymentno`,gy.`number`,gy.`freightamount`,gy.`estimate_freight`,gy.`start_weights`,gy.`end_weights`,gy.`cost_weights`,gy.`cname`,gy.`bankname`,gy.`bankcode`,gy.`status`,gy.`pay_type`,gy.`created_at`,gy.`dealno` FROM payment_order gy WHERE c_id='.intval($params['c_id']);
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
     }

  








}