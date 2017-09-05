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
            $filter[] = " gl_return_car.`status`=" . $params['status'];
        }
        if (isset($params['cid']) && $params['cid'] != '') {

            $filter[] = " gl_return_car.`cid`=" . $params['cid'];
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }

        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1)
                FROM `gl_return_car`
                {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT gl_return_car.`id`,
                       gl_return_car.`start_province_id`,
                       gl_return_car.`start_city_id`,
                       gl_return_car.`start_area_id`,
                       gl_return_car.`end_province_id`,
                       gl_return_car.`end_city_id`,
                       gl_return_car.`end_area_id`,
                       gl_return_car.`start_time`,
                       gl_return_car.`end_time`,
                       gl_return_car.`price_type`,
                       gl_return_car.`min_load`,
                       gl_return_car.`max_load`,
                       gl_return_car.`category_id`,
                       gl_return_car.`product_id`,
                       gl_return_car.`inquiry_id`,
                       gl_return_car.`order_id`,
                       gl_return_car.`price`,
                       gl_return_car.`status`,
                       gl_inquiry.`gid` as goods_id
                     FROM `gl_return_car`
                     LEFT JOIN gl_inquiry  ON gl_return_car.`id` = gl_inquiry.`car_id`  {$where}
                     ORDER BY gl_return_car.status=3 ASC, gl_return_car.`{$order}` DESC";
        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }
   //获取详细
    public function getInfo($id){
        $sql = "SELECT
                        `id`,
                        `cid`,
                        `start_province_id`,
                        `start_city_id`,
                        `start_area_id`,
                        `end_province_id`,
                        `end_city_id`,
                        `end_area_id`,
                        `start_time`,
                        `end_time`,
                        `price_type`,
                        `min_load`,
                        `max_load`,
                        `category_id`,
                        `product_id`,
                        `price`,
                        `status`
                        FROM `gl_return_car` WHERE `id` = {$id}  AND `is_del`= 0";

      /*  $sql = "SELECT gl_return_car.`id`,
                        gl_return_car.`cid`,
                        gl_return_car.`start_province_id`,
                        gl_return_car.`start_city_id`,
                        start_city.`city` as start_city,
                        gl_return_car.`start_area_id`,
                        start_area.`area` as start_area,
                        gl_return_car.`end_province_id`,
                        gl_return_car.`end_city_id`,
                        end_city.`city` as end_city,
                        gl_return_car.`end_area_id`,
                        end_area.`area` as end_area,
                        gl_return_car.`start_time`,
                        gl_return_car.`end_time`,
                        gl_return_car.`price_type`,
                        gl_return_car.`min_load`,
                        gl_return_car.`max_load`,
                        gl_return_car.`category_id`,
                        gl_return_car.`product_id`,
                        gl_return_car.`price`,
                        gl_return_car.`status`
                        FROM `gl_return_car`
                        LEFT JOIN conf_city  start_city ON gl_return_car.`start_city_id` = start_city.`cityid`
                        LEFT JOIN conf_city  end_city  ON gl_return_car.`end_city_id` = end_city.`cityid`
                        LEFT JOIN conf_area  start_area ON gl_return_car.`start_area_id` = start_area.`areaid`
                        LEFT JOIN conf_area  end_area  ON gl_return_car.`end_area_id` = end_area.`areaid`
                        WHERE gl_return_car.`id` = {$id}  AND gl_return_car.`is_del`= 0";*/
      
        return $this->dbh->select_row($sql);
    }
    //智能发布获取回程车
    public function fastBackCar($params){

        $filter[] = " WHERE 1=1 AND gl_order.`status` in(2,3)  AND gl_order.`is_release`=0 ";
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
                        gl_goods.product_id,
                        gl_goods.weights,
                        gl_goods.status,
                        gl_goods.reach_endtime,
                        gl_products.zh_name as product_name
                        FROM gl_order
                      LEFT JOIN gl_goods  ON gl_order.`goods_id` = gl_goods.`id`
                      LEFT JOIN gl_products ON gl_products.id = gl_goods.product_id {$where}";

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
                        gl_goods.product_id,
                        gl_goods.weights,
                        gl_goods.status,
                        gl_goods.status,
                        gl_goods.reach_endtime
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
                    $input = array(
                        'cid'=> $v['company_id'],
                        'start_province_id'=>$v['end_provice_id'],
                        'start_city_id'=>$v['end_city_id'],
                        'start_area_id'=>$v['end_area_id'],
                        'end_province_id'=>$v['start_provice_id'],
                        'end_city_id'=>$v['start_city_id'],
                        'end_area_id'=>$v['start_area_id'],
                        'category_id'=>$v['cate_id'],
                        'product_id'=>$v['product_id'],
                        'start_time'=>$v['reach_endtime'],
                        'end_time'=>$v['reach_endtime'],
                        'price_type'=>$price_type_arr[$k],
                        'price'=>$price_arr[$k],
                        'created_at'=>'=NOW()',
                        'updated_at'=>'=NOW()',
                    );

                    if($weights_type_arr[$k]==1){
                        $input['min_load'] =$v['weights'];
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