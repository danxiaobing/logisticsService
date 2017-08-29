<?php

/**
 * 询价单管理
 * User: Andy
 */
class Transmanage_OrderModel
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
            $filter[] = " o.`starttime` <= '{$params['starttime']}'";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " o.`off_starttime` >= '{$params['endtime']}'";
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " o.`reach_starttime` = '{$params['status']}'";
        }

        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " o.`company_id` = '{$params['cid']}'";
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
               g.start_provice_id,
               g.start_city_id,
               g.end_provice_id,
               g.end_city_id,
               g.cate_id,
               g.product_id,
               g.weights,
               g.companies_name,
               g.off_starttime,
               g.off_endtime,
               g.reach_starttime,
               g.reach_endtime,
               g.companies_name,
               o.status,
               o.id,
               o.created_at,
               o.number,
               p.zh_name
                FROM gl_order as o 
                LEFT JOIN gl_goods as g ON g.id = o.goods_id
                LEFT JOIN gl_products as p ON p.id = g.product_id
                WHERE  {$where}
                ORDER BY id DESC 
                ";

        if(isset($params['id']) && $params['id'] != ''){
            $result['list'] = $this->dbh->select($sql);
        }else{
            $result['list'] = $this->dbh->select_page($sql);
        }


        if(!empty($result['list'])){
            $city = array_column($this->dbh->select('SELECT cityid,city FROM conf_city'),'city','cityid');
            foreach($result['list'] as $key=>$value){
                $result['list'][$key]['start_city'] = $city[$value['start_city_id']];
                $result['list'][$key]['end_city'] = $city[$value['end_city_id']];
            }
            unset($city);
        }

        return $result;
    }

    /*获取单个托运单的详情*/
    public function getOrderInfo($orderid){
      //获取托运单基本信息
      $sql = "SELECT go.number,go.goods_id,go.estimate_freight FROM gl_order go WHERE go.id=".intval($orderid);
      $info = $this->dbh->select_row($sql);

      //获取goods基本信息
      $sql = "SELECT gd.id, gd.cid ,gd.start_provice_id ,gd.start_city_id ,gd.end_provice_id ,gd.end_city_id ,gd.weights ,gd.price ,gd.companies_name ,gd.off_starttime ,gd.off_endtime ,gd.reach_starttime ,gd.reach_endtime ,gd.offer_status,gd.offer_price,gd.loss,gd.desc_str ,gd.off_address ,gd.off_user ,gd.off_phone ,gd.reach_address ,gd.reach_user ,gd.reach_phone ,gd.consign_user ,gd.consign_phone,gp.zh_name,gct.`name`,gd.created_at,gd.`status` FROM gl_goods gd LEFT JOIN  gl_products gp ON gp.id = gd.product_id LEFT JOIN gl_cars_type gct ON  gct.id=gd.cars_type WHERE gd.id =".$info['goods_id'];
      $data = $this->dbh->select_row($sql);

      //获取城市信息
      $city = $this->dbh->select('SELECT cityid,city FROM conf_city');
      //获取托运单的调度信息
      $sql = "SELECT
                god.`id`,
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
                god.`order_id` = ".intval($orderid);
      $res = $this->dbh->select($sql);
      $Schedule =  $res ? $res:[];
      return array('info'=>$info,'data'=>$data,'city'=>$city,'schedule'=>$Schedule);
    }


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

        $sql = "SELECT id,status,goods_id FROM gl_order   WHERE {$where}";

        $orderArr = $this->dbh->select_row($sql);

        if(empty($orderArr)){
            return false;
        }

        #开启事物
        $this->dbh->begin();
        try{
            $orderArr['status'] = $params['status'];
            $orderArr['reasons'] = !empty($params['reasons']) ? $params['reasons']:'';
            $order = $this->dbh->update('gl_order',$orderArr,'id = '.$orderArr['id']);

            if(empty($order)){
                $this->dbh->rollback();
                return false;
            }

            if (isset($params['cargo_id'])  && $params['status'] == 6) {
                $goodArr = $this->dbh->select_row('SELECT status,source,reach_endtime FROM gl_goods WHERE id = ' . $orderArr['goods_id']);
                if (!empty($goodArr) && $goodArr['source'] == 0) {
                    $goodArr['status'] = time() > strtotime($goodArr['reach_endtime']) ? 3 : 1;
                    $good = $this->dbh->update('gl_goods', $goodArr, 'id = ' . $orderArr['goods_id']);

                    if (empty($good)) {
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

}