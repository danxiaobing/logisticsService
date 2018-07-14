<?php

/**
 * 询价单管理
 * User: Andy
 */
class Transmanage_OrderModel
{
    public $dbh = null;
    public $dbh2 = null;

    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null,$dbh2)
    {
        $this->dbh = $dbh;
        $this->dbh2 = $dbh2;
    }

    public function searchOrder($params){
        $filter = array();

        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " g.`start_provice_id` =".$params['start_provice_id'];
        }

        if (isset($params['start_city_id']) && $params['start_city_id'] != '') {
            $filter[] = " g.`start_city_id` =".$params['start_city_id'];
        }

        if (isset($params['start_area_id']) && $params['start_area_id'] != '') {
            $filter[] = " g.`start_area_id` =".$params['start_area_id'];
        }

        if (isset($params['end_provice_id']) && $params['end_provice_id'] != '') {
            $filter[] = " g.`end_provice_id` =".$params['end_provice_id'];
        }

        if (isset($params['end_city_id']) && $params['end_city_id'] != '') {
            $filter[] = " g.`end_city_id` =".$params['end_city_id'];
        }


        if (isset($params['end_area_id']) && $params['end_area_id'] != '') {
            $filter[] = " g.`end_area_id` =".$params['end_area_id'];
        }


        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " o.`created_at` >= '{$params['starttime']} 00:00:00'";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " o.`updated_at` <= '{$params['endtime']} 23:59:59'";
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " o.`status` = '{$params['status']}'";
        }

        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " o.`company_id` = '{$params['cid']}'";
        }

        if (isset($params['min']) && $params['min'] != '') {
            $filter[] = " g.`weights` >= '{$params['min']}'";
        }

        if (isset($params['max']) && $params['max'] != '') {
            $filter[] = " g.`weights` <= '{$params['max']}'";
        }

        if (isset($params['keyworks']) && $params['keyworks'] != '') {
            $filter[] = " o.`number` like '%{$params['keyworks']}%' ";
        }


        if(isset($params['id']) && $params['id'] != ''){
            $order = substr($params['id'],0,strlen($params['id'])-1);
            $filter[] = "o.`id` in({$order})";

        }else{
            $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
            $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);
        }
        $where = ' o.`is_del` = 0 ';

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_order AS o LEFT JOIN gl_goods AS g ON g.`id` = o.`goods_id`  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);


        $sql = "SELECT 
               g.start_provice,
               g.start_city,
               g.end_provice,
               g.end_city,
               g.product_name,
               g.weights,
               g.companies_name,
               g.off_starttime,
               g.off_endtime,
               g.reach_starttime,
               g.reach_endtime,
               g.companies_name,
               g.weights_done,
               g.reach_address,
               g.off_address,
               o.status,
               o.id,
               o.created_at,
               o.number
                FROM gl_order as o
                LEFT JOIN gl_goods as g ON g.id = o.goods_id
                WHERE  {$where}
                ORDER BY id DESC 
                ";

        if(isset($params['id']) && $params['id'] != ''){
            $result['list'] = $this->dbh->select($sql);
        }else{
            $result['list'] = $this->dbh->select_page($sql);
        }

        return $result;
    }

    /*获取单个托运单的详情*/
    public function getOrderInfo($orderid){
      //获取托运单基本信息
      $sql = "SELECT go.id,go.number,go.cargo_id,go.company_id,go.goods_id,go.estimate_freight,go.status,go.pdf_url,go.fact_freight,gi.price FROM gl_order go LEFT JOIN gl_inquiry gi ON go.id = gi.order_id  WHERE go.id=".intval($orderid);
      $info = $this->dbh->select_row($sql);
      //获取goods基本信息
      $sql = "SELECT
                     gd.id,
                     gd.cid,
                     gd.start_provice_id,
                     gd.start_city_id,
                     gd.start_area_id,
                     gd.end_provice_id,
                     gd.end_city_id,
                     gd.end_area_id,
                     gd.start_provice,
                     gd.start_city,
                     gd.start_area,
                     gd.end_provice,
                     gd.end_city,
                     gd.end_area,
                     gd.product_id,
                     gd.product_name,
                     gd.cars_type_name,
                     gd.weights,
                     gd.weights_done,
                     gd.price ,
                     gd.companies_name ,
                     gd.off_starttime ,
                     gd.off_endtime ,
                     gd.reach_starttime ,
                     gd.reach_endtime ,
                     gd.offer_status,
                     gd.offer_price,
                     gd.loss,
                     gd.desc_str ,
                     gd.off_address ,
                     gd.off_user ,
                     gd.off_phone ,
                     gd.reach_address ,
                     gd.reach_user ,
                     gd.reach_phone ,
                     gd.consign_user ,
                     gd.consign_phone,
                     gd.created_at,
                     gd.`status`,
                     gd.pay_type,
                     gd.`qq`
                     FROM gl_goods gd WHERE gd.id =".$info['goods_id'];
      $data = $this->dbh->select_row($sql);


      //获取托运单的调度信息

        if(!empty($data)){
            $sql = "SELECT
                god.`id`,
                god.`order_id`,
                god.`cars_number` ,
                god.`driver_name` ,
                gd.`mobile` ,
                god.`weights` ,
                god.`start_weights` ,
                god.`end_weights` ,
                god.`status`
              FROM
                gl_order_dispatch god
              LEFT JOIN
                gl_driver gd
              ON
                gd.id = god.driver_id
              WHERE
                god.`order_id` = ".intval($orderid)." AND god.`status` != 6";
            $res = $this->dbh->select($sql);
        }

      $Schedule =  $res ? $res:[];
      return array('info'=>$info,'data'=>$data,'schedule'=>$Schedule);
    }


    /**
     * 货主取消交易
     * @param $params
     * @author daley
     * @return bool
     */
    public function untreadOrder($params){

        $where = ' gl_order.`is_del` = 0 ';
        $filter = [];

        if (isset($params['id']) && $params['id'] != '') {
            $filter[] = " gl_order.`id` =".$params['id'];
        }

        if (isset($params['companies_id']) && $params['companies_id'] != '') {
            $filter[] = " gl_order.`company_id` =".$params['companies_id'];
        }
        if (isset($params['cargo_id']) && $params['cargo_id'] != ''){
            $filter[] = " gl_order.`cargo_id` =".$params['cargo_id'];
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT id,status,goods_id,car_id FROM gl_order   WHERE {$where}";

        $orderArr = $this->dbh->select_row($sql);

        if(empty($orderArr)){
            return false;
        }

        #开启事物
        $this->dbh->begin();
        try{
            $orderArr['status'] = $params['status'];
            //$orderArr['reasons'] = !empty($params['reasons']) ? $params['reasons']:'';
            $order = $this->dbh->update('gl_order',$orderArr,'id = '.$orderArr['id']);

            if(empty($order)){
                $this->dbh->rollback();
                return false;
            }

            if (isset($params['cargo_id'])  && $params['status'] == 6) {
                $goodArr = $this->dbh->select_row('SELECT status,source,reach_starttime FROM gl_goods WHERE id = ' . $orderArr['goods_id']);
                if (!empty($goodArr) && $goodArr['source'] == 0) {

                    if($goodArr['reach_starttime']!== '0000-00-00 00:00:00'){
                        $goodArr['status']  = time() > strtotime($goodArr['reach_starttime']) ? 3 : 1;
                    }else{
                        $goodArr['status'] = 1;
                    }
                    $good = $this->dbh->update('gl_goods', $goodArr, 'id = ' . $orderArr['goods_id']);

                    if (empty($good)) {
                        $this->dbh->rollback();
                        return false;
                    }
                }
                //car_id 不为代表是从货主查找车源添加的信息
                //不为空代表是回程车信息
                if(!empty($orderArr['car_id'])){
                    //修改回程车信息状态

                    $sql = "SELECT `id`,`end_time` FROM gl_return_car WHERE `id` =".$orderArr['car_id'];
                    $return_car = $this->dbh->select_row($sql);
                    if(!$return_car){
                        $this->dbh->rollback();
                        return false;
                    }
                    $info['status']  = time() > strtotime($return_car['end_time']) ? 3:1;
                    $info['inquiry_id'] = 0;
                    $info['order_id']  = 0;
                    $result = $this->dbh->update('gl_return_car',$info,'id ='.$return_car['id']);
                    if(!$result){
                        $this->dbh->rollback();
                        return false;
                    }
                }
                //修改调度单状态
                $order_dispatchArr = $this->dbh->select_row('SELECT status FROM gl_order_dispatch WHERE is_del= 0 AND order_id = '. $orderArr['id']);
                if(!empty($order_dispatchArr)){
                    $update['is_del'] = 1;
                    $res = $this->dbh->update('gl_order_dispatch',$update,'order_id = '.$orderArr['id']);
                    if (empty($res)) {
                        $this->dbh->rollback();
                        return false;
                    }
                }
            }

            $this->dbh->commit();
            return true;


        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }

    }

    /*获取物流详情*/
    public function getlist($dispatchid){
      //调度单日志
     $sql = "SELECT
              godl.`id` ,
              godl.`dispatch_id` ,
              godl.`status` ,
              godl.`created_at`
            FROM
              gl_order_dispatch_log godl
            WHERE godl.dispatch_id = ".intval($dispatchid)."
            ORDER BY
              godl.`status` ASC";
     $res = $this->dbh->select($sql);

     if(empty($res)){
        return false;
     }

     $sql = "SELECT DISTINCT
               id ,
               dispatch_id ,
               pic ,
               `STATUS`
             FROM
               gl_order_dispatch_pic
             WHERE
               dispatch_id = ".intval($dispatchid).'
             order by `status` asc';
      $pic =  $this->dbh->select($sql);

    
      foreach ($res as $key => $value) {
        $res[$key]['pic'] = '';
        if(!empty($pic)){
          if($value['status'] == 3){
            $res[$key]['pic'] = $pic[0]['pic'];
          }elseif($value['status'] == 5){
            $res[$key]['pic'] = $pic[1]['pic'];
          }
        }
      }
  



     //单据凭证
     // $sql = 'SELECT pic,status FROM gl_order_dispatch_pic   godp WHERE is_del = 0 AND dispatch_id='.intval($dispatchid);
     // $pic = $this->dbh->select($sql);

     return array('data'=> $res);
    }


    /*获取托运单生成时间*/
    public function getTime($orderid){
      $sql = "SELECT created_at FROM gl_order WHERE id=".intval($orderid);
      return $this->dbh->select_one($sql);
    }

    /*智运后台获取托运单数据*/
    public function getOrderList($serach){
      $filter = array();
      if(isset($serach['number']) && $serach['number'] != ''){
        $filter[] = " god.`order_number` = '{$serach['number']}' ";
      }
      if(isset($serach['dispatch_number']) && $serach['dispatch_number'] != ''){
        $filter[] = " god.`dispatch_number` = '{$serach['dispatch_number']}' ";
      } 
      if(isset($serach['carryname']) && $serach['carryname'] != ''){
        $filter[] = " god.`c_name` like '%{$serach['carryname']}%' ";
      } 

      $WHERE = " WHERE god.`is_del` = 0 ";
      if(count($filter)>0){
        $WHERE .= " AND ".implode(' AND ', $filter);
      }

      //获取总数
      $sql = "SELECT count(1) FROM gl_order_dispatch god {$WHERE}";

      $result['totalRow']= $this->dbh->select_one($sql);
      $result['list'] = array();


        if($result['totalRow']){
            //总的页数
            $result['totalPage']  = ceil($result['totalRow'] / $serach['pageSize']);  
            //设置当前页 和 pagesize
            $this ->dbh ->set_page_num($serach['pageCurrent']);
            $this ->dbh ->set_page_rows($serach['pageSize']); 
            //数据获取
            $sql = "SELECT god.`order_id`,god.`order_number`,go.`cargo_id`,god.`c_name` cname,god.`start_provice_id`,god.`end_provice_id`,god.`weights`,god.`start_weights`,god.`end_weights` FROM gl_order_dispatch god LEFT JOIN gl_order go  ON go.id = god.order_id LEFT JOIN gl_companies gc ON gc.id = go.company_id {$WHERE} ORDER BY god.id  DESC";
            $result['list'] = $this->dbh->select_page($sql);

            //获取省名
            $sql = "SELECT provinceid,province FROM conf_province";
            $province = $this->dbh->select($sql);
            $province = array_column($province,'province','provinceid');

            //获取货主公司名称
            //$sql = "SELECT IFNULL(company_name,'') name  FROM td_companies WHERE id=";
            foreach ($result['list'] as $k => $val) {
                if(is_null($val['cargo_id'])){
                    $result['list'][$k]['cargoname'] = '';
                }else{
                    $sql = "SELECT IFNULL(company_name,'') name  FROM td_companies WHERE id=".$val['cargo_id'];
                    $name = $this->dbh2->select_one($sql);
                    $result['list'][$k]['cargoname'] = $name ? $name : '';
                }

                $result['list'][$k]['startp'] = $province[$val['start_provice_id']];
                $result['list'][$k]['endp'] = $province[$val['end_provice_id']];
            }

        }

        return $result;



    }


    /**
     * 确定金额
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function payOrder($id,$params){
        $order = $this->dbh->select_row('SELECT goods_id,status FROM gl_order WHERE  status = 4 AND is_del= 0 AND id = '.intval($id));

        if(!$order){
            return false;
        }

        #开启事物
        $this->dbh->begin();
        try{
            $data = $this->dbh->update('gl_order',$params,'id = '.intval($id));
            $goods = $this->dbh->update('gl_goods',['status'=>6],' id ='.$order['goods_id']);

            if(!$data && !$goods){
                $this->dbh->rollback();
                return false;
            }

            $this->dbh->commit();
            return true;


        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }


    /**
     * 修改托运单
     * @param int $id
     * @param array $params
     * @return mixed
     */
    public function updateOrder($id,$params){

        return $this->dbh->update('gl_order', $params, 'id = '.intval($id));
    }

    public function retreatOrder($id,$params){
        #开启事物
        $this->dbh->begin();
        try{
            $order = $this->dbh->update('gl_order', $params, 'id = '.intval($id));
            $dispatch = $this->dbh->update('gl_order_dispatch',['status'=>6],'order_id = '.intval($id));

            if(!$order && !$dispatch){
                $this->dbh->rollback();
                return false;
            }

            $this->dbh->commit();
            return true;

        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }

    }




    public function createpay($param){
        $param['paymentno'] = $this->get_random($len=4);
        //事务
        $this->dbh->begin();
        try{
            $res = $this->dbh->insert('payment_order',$param);
            if(!$res){
             $this->dbh->rollback();
             return false; 
            }
            $this->dbh->commit();
            return $res;
            
        }catch(Exception $e){
             $this->dbh->rollback();
            return false;           
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


}