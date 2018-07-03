<?php

/**
 * 收付款单-付款单关联订单表
 */
class Payment_OrderModel
{
    public $dbh = null;
    public $gy_db = null;

    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $gy_db = null)
    {
        $this->dbh = $dbh;
        $this->gy_db = $gy_db;
    }

    /**
     * 获得订单的所有信息
     */
    public function getList($params){
        $filter = array();

        $where = ' g.isdel = 0 ';

        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " g.`cargo_id` =".$params['cid'];
        }


        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " g.`status` = '{$params['status']}'";
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

        //print_r($filter);die;
        $sql = "SELECT count(1) FROM payment_order g  WHERE {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);
        

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT g.*
                FROM payment_order g
                WHERE  {$where}
                ORDER BY g.id DESC";
        // print_r($sql);die;
        $result['list']  = $this->dbh->select_page($sql);
        return $result;
    }



    /**
     * 获取货源询价单详情
     * @param $id
     * @return mixed
     */
    public function getInfo($id){

        //查询询价单信息
        $sql = "SELECT *
               FROM payment_order i
               WHERE i.isdel = 0 AND i.id=".$id." ORDER BY id DESC";
               // print_r($sql);die;
        $result = $this->dbh->select_row($sql);

       //询价单记录信息
        // $sql = "SELECT id,minprice,maxprice,cid,type,updated_at,created_at
        //         FROM gl_inquiry_info WHERE is_del = 0 AND pid=".$id." ORDER BY id ASC";
        // $result['inquiry_info'] = $this->dbh->select($sql);

        return $result;
    }

    /**
     * @param array $params 收付款单-付款单关联订单表
     * @return mixed
     */
    public function addPaymentOrder($params)
    {
        $params['dealno'] = $this->get_random($len=4);
        $params['created_at'] = '=NOW()';
        $params['status'] = 3;

        //计算货损率
        $sql = 'SELECT sum(start_weights) as s,sum(end_weights) as e FROM gl_order_dispatch WHERE order_id='.intval($params['order_id']);
        $nums = $this->dbh->select_row($sql);
        $loss = $nums['s'] != 0? ($nums['s']-$nums['e'])/$nums['s']:0.00;
        $loss = round($loss*100,1);
        $params['loss'] = $loss;

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

        //搜索条件
        $where = '';

        if(isset($params['number']) && $params['number'] != ''){
            $filter[] = 'number = "'.$params['number'].'"';
        }

        if(isset($params['status']) && $params['status'] != -100){
            $filter[] = 'status = '.intval($params['status']);
        }

        if(isset($params['c_id']) && $params['c_id'] != 0){
            $filter[] = 'c_id = '.intval($params['c_id']);
        }

        if(count($filter)){
            $str = implode(' AND ', $filter);
            $where .= $str;
        }

        //计算总数
        $sql = "SELECT count(1) FROM payment_order WHERE {$where}";

        $data = $this->dbh->select_one($sql);

        $result['totalRow'] = $data ? $data:0;

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

        $sql = "SELECT gy.`id`,gy.`c_id`,gy.`cargo_id`,gy.`order_id`,gy.`goods_id`,gy.`paymentno`,gy.`number`,gy.`freightamount`,gy.`estimate_freight`,gy.`start_weights`,gy.`end_weights`,gy.`cost_weights`,gy.`cname`,gy.`bankname`,gy.`bankcode`,gy.`status`,gy.`pay_type`,gy.`created_at`,gy.`dealno`,gy.`remark` FROM payment_order gy WHERE {$where} ORDER BY gy.`id` DESC";
    // var_dump($sql);die;
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }


    public function getpayinfo($payid){
        $sql  = 'SELECT gy.`id`,gy.`c_id`,gy.`cargo_id`,gy.`order_id`,gy.`goods_id`,gy.`paymentno`,gy.`number`,gy.`freightamount`,gy.`estimate_freight`,gy.`start_weights`,gy.`end_weights`,gy.`cost_weights`,gy.`cname`,gy.`bankname`,gy.`bankcode`,gy.`status`,gy.`pay_type`,gy.`created_at`,gy.`dealno`,gy.`remark`,gy.`loss` FROM payment_order gy WHERE id='.intval($payid);
        $data = $this->dbh->select_row($sql);
        return $data ? $data : array() ;
    }

    //更新结算单
    public function updatepay($params){
        $id = $params['id'];
        unset($params['id']);
        $params['updated_at'] = '=NOW()';
        $params['status'] = 3;
        $res = $this->dbh->update('payment_order',$params,'id='.intval($id));
        return $res ? true : false;
    }

    //更新
    public function update($params){
        $id = $params['id'];
        unset($params['id']);
        $params['updated_at'] = '=NOW()';
        $res = $this->dbh->update('payment_order',$params,'id='.intval($id));
        return $res ? true : false;
    }
    
    public function getbankinfo($id){
        $sql = "select * from td_companies_account WHERE companies_id = {$id} and status = 1";
        return $this->gy_db->select_row($sql);
    }


    public function infoByorderid($orderid){
        $sql = 'SELECT gy.`id`,gy.`c_id`,gy.`cargo_id`,gy.`order_id`,gy.`goods_id`,gy.`paymentno`,gy.`number`,gy.`freightamount`,gy.`estimate_freight`,gy.`start_weights`,gy.`end_weights`,gy.`cost_weights`,gy.`cname`,gy.`bankname`,gy.`bankcode`,gy.`status`,gy.`pay_type`,gy.`created_at`,gy.`dealno` FROM payment_order gy WHERE order_id='.intval($orderid).' order by id asc limit 1';
        $data = $this->dbh->select_row($sql);

        return $data ? $data : array() ;        
    }




}