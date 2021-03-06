<?php

/**
 * 回程车
 * User: Daley
 */
class Transmanage_ReturnCarModel
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


    public function getPage($params)
    {

        $filed = array();
        $filter[] = " WHERE 1=1";
        $where = "  ";
        $order = "updated_at";

        if (isset($params['order']) && $params['order'] != '') {
            $order = $params['order'];
        }
        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " r.`status`=" . $params['status'];
        }
        if (isset($params['cid']) && $params['cid'] != '') {

            $filter[] = " r.`cid`=" . $params['cid'];
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }

        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        /** 新增对回程车发车时间判断  **/
        $date = date('Y-m-d');
        $filter_where = "WHERE  com.`is_del` = 0 AND r.`is_del` = 0 AND r.`status` = 1  AND r.`end_time`<'{$date}'";
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter_where .= " AND r.`cid`=" . $params['cid'];
        }
        $sql = "SELECT r.id,r.end_time,r.status FROM gl_return_car AS r LEFT JOIN gl_companies AS com ON com.id = r.cid {$filter_where}";
        $list = $this->dbh->select($sql);
        if($list){
            foreach($list as $key=>$val){
                $info['status'] = 3;
                $this->dbh->update('gl_return_car',$info,'id ='.$val['id']);
            }
        }
        /** 对回程车发车时间判断 **/

        $sql = "SELECT count(1)
                FROM `gl_return_car` r
                {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT  r.`id`,
                         r.`start_province_id`,
                         r.`start_city_id`,
                         r.`start_area_id`,
                         r.`end_province_id`,
                         r.`end_city_id`,
                         r.`end_area_id`,
                         r.`start_time`,
                         r.`end_time`,
                         r.`price_type`,
                         r.`min_load`,
                         r.`max_load`,
                         r.`category_id`,
                         r.`product_id`,
                         r.`inquiry_id`,
                         r.`order_id`,
                         r.`price`,
                         r.`status`,
                        gl_inquiry.`gid` as goods_id,
                        gl_order.`status` as order_status
                     FROM `gl_return_car` r
                     LEFT JOIN gl_inquiry  ON  r.`id` = gl_inquiry.`car_id`
                     LEFT JOIN gl_order  ON  r.`order_id` = gl_order.`id`  {$where}
                     ORDER BY  r.status=3 ASC,  r.`{$order}` DESC";
        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }
   //获取详细
    public function getInfo($id){

   $sql = "SELECT   r.`id`,
                        r.`cid`,
                        r.`start_province_id`,
                        r.`start_city_id`,
                        start_city.`city` as start_city,
                        r.`start_area_id`,
                        start_area.`area` as start_area,
                        r.`end_province_id`,
                        r.`end_city_id`,
                        end_city.`city` as end_city,
                        r.`end_area_id`,
                        end_area.`area` as end_area,
                        r.`start_time`,
                        r.`end_time`,
                        r.`price_type`,
                        r.`min_load`,
                        r.`max_load`,
                        r.`category_id`,
                        r.`category_id_two`,
                        r.`loss`,
                        r.`product_id`,
                        r.`price`,
                        r.`status`
                        FROM `gl_return_car`  r
                        LEFT JOIN conf_city  start_city ON r.`start_city_id` = start_city.`cityid`
                        LEFT JOIN conf_city  end_city  ON r.`end_city_id` = end_city.`cityid`
                        LEFT JOIN conf_area  start_area ON r.`start_area_id` = start_area.`areaid`
                        LEFT JOIN conf_area  end_area  ON r.`end_area_id` = end_area.`areaid`
                        WHERE r.`id` = {$id}  AND r.`is_del`= 0";
        return $this->dbh->select_row($sql);
    }
    //智能发布获取回程车
    public function fastBackCar($params){

        $filter[] = " WHERE 1=1 AND gl_order.`status` in(2,3,4,5,8)  AND gl_order.`is_release`=0 ";
        $where = "  ";
        if (isset($params['company_id']) && $params['company_id'] != '') {
            $filter[] = " gl_order.`company_id`=" . $params['company_id'];
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }

        $sql = "SELECT
                        gl_order.id AS order_id,
                        gl_order.goods_id,
                        gl_goods.start_provice_id,
                        gl_goods.start_city_id,
                        gl_goods.start_area_id,
                        gl_goods.end_provice_id,
                        gl_goods.end_city_id,
                        gl_goods.end_area_id,
                        gl_goods.cate_id,
                        gl_goods.cate_id_two,
                        gl_goods.product_id,
                        gl_goods.weights,
                        gl_goods.status,
                        gl_goods.reach_starttime
                        FROM gl_order
                      LEFT JOIN gl_goods  ON gl_order.`goods_id` = gl_goods.`id` {$where}";

        return $this->dbh->select($sql);

    }
    //智能发布
    public function fast($params){

        $filter[] = " WHERE 1=1 AND gl_order.`is_release`= 0 ";
        $where = "  ";
        if (isset($params['order_id_arr']) && $params['order_id_arr'] != '') {
            $order_id_arr = rtrim($params['order_id_arr'], ",");
            $filter[] = " gl_order.`id` in({$order_id_arr}) ";
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }
        $sql = "SELECT
                        gl_order.id,
                        gl_order.company_id,
                        gl_goods.start_provice_id,
                        gl_goods.start_city_id,
                        gl_goods.start_area_id,
                        gl_goods.end_provice_id,
                        gl_goods.end_city_id,
                        gl_goods.end_area_id,
                        gl_goods.cate_id,
                        gl_goods.cate_id_two,
                        gl_goods.product_id,
                        gl_goods.weights,
                        gl_goods.status,
                        gl_goods.loss,
                        gl_goods.status,
                        gl_goods.reach_starttime
                        FROM gl_order
                        LEFT JOIN gl_goods  ON gl_order.`goods_id` = gl_goods.`id`
                       {$where}";

        $info = $this->dbh->select($sql);
        if (isset($params['weights_type_arr']) && $params['weights_type_arr'] != '') {
            $weights_type_arr = explode(',', $params['weights_type_arr']);
        }
        if (isset($params['price_type_arr']) && $params['price_type_arr'] != '') {
            $price_type_arr = explode(',', $params['price_type_arr']);
        }
        if (isset($params['price_arr']) && $params['price_arr'] != '') {
            $price_arr = explode(',', $params['price_arr']);
        }
        if(!empty($info)){

            #开启事物
            $this->dbh->begin();
            try{
                foreach ($info as $k=>$v){

                    if($v['reach_starttime']!== '0000-00-00 00:00:00'){
                        $v['reach_starttime'] = substr($v['reach_starttime'],0,10);
                    }else{
                        $v['reach_starttime'] = date('Y-m-d');
                    }
                    $input = array(
                        'cid'=> $v['company_id'],
                        'start_province_id'=>$v['end_provice_id'],
                        'start_city_id'=>$v['end_city_id'],
                        'start_area_id'=>$v['end_area_id'],
                        'end_province_id'=>$v['start_provice_id'],
                        'end_city_id'=>$v['start_city_id'],
                        'end_area_id'=>$v['start_area_id'],
                        'category_id'=>$v['cate_id'],
                        'category_id_two'=>$v['cate_id_two'],
                        'product_id'=>$v['product_id'],
                        'start_time'=>$v['reach_starttime'],
                        'end_time'=>date('Y-m-d', strtotime ("+7 day", strtotime($v['reach_starttime']))),
                        'price_type'=>$price_type_arr[$k],
                        'price'=>$price_arr[$k],
                        'loss'=>$v['loss'],
                        'created_at'=>'=NOW()',
                        'updated_at'=>'=NOW()',
                    );

                    if($weights_type_arr[$k]==1){
                        $input['min_load'] =$v['weights'];
                        $input['max_load'] = 2500;
                    }else{
                        $input['max_load'] = $v['weights'];
                    }

                    $data = $this->dbh->insert('gl_return_car',$input);
                    if(empty($data)){
                        $this->dbh->rollback();
                        return false;
                    }else{
                        //更新运单表
                        $update = array('is_release'=>1);
                        $res = $this->dbh->update('gl_order',$update,'id='.intval($v['id']));
                        if(empty($res)){
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

        }else{
            return false;
        }

    }
    //添加信息
    public function addInfo($params)
    {
        return $this->dbh->insert('gl_return_car',$params);
    }
    //修改信息
    public function update($params, $id)
    {
        return $this->dbh->update('gl_return_car',$params,'id=' . intval($id));
    }
    //删除
    public function del($id)
    {
        return $this->dbh->delete('gl_return_car','id=' . intval($id));
    }


}