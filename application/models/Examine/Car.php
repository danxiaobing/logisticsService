<?php

/**
 * User: Jeff
 */
class Examine_CarModel
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
        $filter[] = " WHERE c.`is_del` = 0";
        $where = "  ";

        if (isset($params['keyworks']) && $params['keyworks'] != '') {
            $filter[] = " 
                ( 
                    c.`number` LIKE '%" .trim($params['keyworks']). "%' 
                    OR d.`name` LIKE '%" .trim($params['keyworks']). "%' 
                    OR d.`mobile` LIKE '%" .trim($params['keyworks']). "%'
                    OR d2.`name` LIKE '%" .trim($params['keyworks']). "%'
                    OR d2.`mobile` LIKE '%" .trim($params['keyworks']). "%'
                    OR f.`name` LIKE '%" .trim($params['keyworks']). "%'
                )";
        }


        if (isset($params['number']) && $params['number'] != '') {
            $filter[] = " c.`number` LIKE '%" . trim($params['number']) . "%'";
        }
        if (isset($params['vins']) && $params['vins'] != '') {
            $filter[] = " c.`vins` LIKE '%" . trim($params['vins']) . "%'";
        }
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filter[] = " com.`company_name` LIKE '%" . trim($params['company_name']) . "%'";
        }
        if (isset($params['fleets_id'])) {
             $filter[] = " c.`fleets_id`=" .  intval($params['fleets_id']);
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1)
                FROM `gl_cars` AS c
                LEFT JOIN `gl_companies` AS com ON com.`id` = c.`company_id`
                LEFT JOIN `gl_fleets` AS f ON f.`id` = c.`fleets_id`
                LEFT JOIN `gl_driver` AS d ON d.`id` = c.`driver_id`
                LEFT JOIN `gl_driver` AS d2 ON d2.`id` = c.`escort_id`
                {$where}";
        //
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT 
                c.`id`,
                c.`number`,
                c.`type`,
                c.`vins`,
                c.`register`,
                c.`engine_number`,
                c.`material`,
                c.`car_position`,
                c.`is_use`,

                com.`company_name`,
                f.`name` as fleets_name,
                d.`name` as driver_name,
                d.`mobile` as driver_mobile,
                d2.`name` as escort_name,
                d2.`mobile` as escort_mobile
                FROM `gl_cars` AS c
                LEFT JOIN `gl_companies` AS com ON com.`id` = c.`company_id`
                LEFT JOIN `gl_fleets` AS f ON f.`id` = c.`fleets_id`
                LEFT JOIN `gl_driver` AS d ON d.`id` = c.`driver_id`
                LEFT JOIN `gl_driver` AS d2 ON d2.`id` = c.`escort_id`
                {$where} 
                ORDER BY c.`updated_at` DESC";
        //print_r($sql);die;
        $result['list'] = $this->dbh->select_page($sql);
        //print_r($result);die;
        return $result;
    }

    public function add($params)
    {
        
        $file = $params['file'];
        unset($params['file']);

        $res = $this->dbh->insert('gl_cars', $params);
        //echo "<pre>";print_r($res);echo "</pre>";die; 
        if( $res ){
            foreach ($file as $key => $value) {
                $value['cars_id'] = $res;
                $this->dbh->insert('gl_cars_pic', $value );
            }
            return $res;
        }
        return false;
    }

    public function update($params, $id)
    {
        $file = $params['file'];
        unset($params['file']);

        $res = $this->dbh->update('gl_cars', $params, 'id ='.$id);
        if( $res ){
            $re = $this->dbh->update('gl_cars_pic', array('is_del'=>1), 'cars_id ='.$id);
            foreach ($file as $key => $value) {
                $value['cars_id'] = $id;
                $this->dbh->insert('gl_cars_pic', $value );
            }
            return $res;
        }
        return false;
    }

    /**
     * 根据id获得细节
     * id: 权限id
     * @return 数组
     */
    public function getInfo($id = 0)
    {
        $sql = "SELECT * FROM gl_cars WHERE id=".$id;
        return $this->dbh->select_row($sql);
    }

    /**
     * 删除
     */
    public function del($id,$data){
        $res = $this->dbh->update('gl_cars',$data,'id = ' . intval($id));
        return $res;
    }

    /**
     * 查询专线车-回程车
     */
    public function getBackAndLineCarPage($params){
        $filed = array();
        $filter_r[] = " WHERE r.`is_del` = 0";//回程车
        $filter_z[] = " WHERE z.`is_del` = 0 AND z.`set_line` = 1 ";//专线车
        $where_r = "  ";
        $where_z = "  ";

        //筛选承运商
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter_r[] = " r.`cid` = " . intval($params['cid']);
            $filter_z[] = " z.`cid` = " . intval($params['cid']);
        }
        //筛选起始省份
        if (isset($params['start_provice_id']) && !empty($params['start_provice_id'])) {
            $filter_r[] = " r.`start_provice_id` = " . intval($params['start_provice_id']);
            $filter_z[] = " z.`start_provice_id` = " . intval($params['start_provice_id']);
        }
        //筛选起始城市
        if (isset($params['start_city_id']) && !empty($params['start_city_id'])) {
            $filter_r[] = " r.`start_city_id` = " . intval($params['start_city_id']);
            $filter_z[] = " z.`start_city_id` = " . intval($params['start_city_id']);
        }
        //筛选起始地区
        if (isset($params['start_area_id']) && !empty($params['start_area_id'])) {
            $filter_r[] = " r.`start_area_id` = " . intval($params['start_area_id']);
            $filter_z[] = " z.`start_area_id` = " . intval($params['start_area_id']);
        }
        //筛选目的省份
        if (isset($params['end_provice_id']) && $params['end_provice_id'] != '') {
            $filter_r[] = " r.`end_provice_id` = " . intval($params['end_provice_id']);
            $filter_z[] = " z.`end_provice_id` = " . intval($params['end_provice_id']);
        }
        //筛选目的城市
        if (isset($params['end_city_id']) && $params['end_city_id'] != '') {
            $filter_r[] = " r.`end_city_id` = " . intval($params['end_city_id']);
            $filter_z[] = " z.`end_city_id` = " . intval($params['end_city_id']);
        }
        //筛选目的地区
        if (isset($params['end_area_id']) && $params['end_area_id'] != '') {
            $filter_r[] = " r.`end_area_id` = " . intval($params['end_area_id']);
            $filter_z[] = " z.`end_area_id` = " . intval($params['end_area_id']);
        }
          //筛选分类
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $filter_r[] = " r.`category_id` = " . intval($params['category_id']);
            $filter_z[] = " p.`category_id` = " . intval($params['category_id']);
        }
           //筛选产品
        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $filter_r[] = " r.`product_id` = " . intval($params['product_id']);
            $filter_z[] = " p.`product_id` = " . intval($params['product_id']);
        }
         //筛选开始时间
        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter_r[] = " r.`start_time` >= '{$params['starttime']}'";
        }
        //筛选结束时间
        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter_r[] = " r.`end_time` <= '{$params['endtime']}'";
        }

        if (count($filter_r) > 0) {
            $where_r .= implode(" AND ", $filter_r);
        }
        if (count($filter_z) > 0) {
            $where_z .= implode(" AND ", $filter_z);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT COUNT(*) FROM(SELECT r.id FROM gl_return_car AS r {$where_r}
                UNION
                SELECT p.id FROM gl_rule AS z  RIGHT JOIN gl_rule_product AS p ON p.rule_id = z.id{$where_z}) AS tt";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT z.id,z.cid,z.car_type,z.price_type,z.price,z.min_load,z.max_load,z.loss,p.product_id,1 AS ctype,com.company_name
                 FROM gl_rule AS z
                 RIGHT JOIN gl_rule_product AS p ON p.rule_id = z.id
                 LEFT JOIN gl_companies AS com ON com.id = z.cid {$where_z}
                UNION
                 SELECT r.id,r.cid,0 AS car_type,r.price_type,r.price,r.min_load,r.max_load,3 AS loss,r.product_id,2 AS ctype,com.company_name
                 FROM gl_return_car AS r
                LEFT JOIN gl_companies AS com ON com.id = r.cid {$where_r}
                ORDER BY id DESC ";
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }

}