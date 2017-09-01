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