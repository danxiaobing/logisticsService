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


        if (isset($params['company_ids']) && count($params['company_ids']) ) {
            $filter[] = " c.`company_id` in (".implode(',',$params['company_ids']).")";
        }

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
        if ( isset($params['fleets_type']) && $params['fleets_type'] != '' ) {
             $filter[] = " c.`fleets_type`=" .  $params['fleets_type'];
        }
        if ( isset($params['status'])) {
             $filter[] = " c.`status` =" .$params['status'];
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
        //echo "<pre>";print_r($filter);echo "</pre>";die;
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this ->dbh ->set_page_num($params['pageCurrent']?$params['pageCurrent']:1);
        $this ->dbh ->set_page_rows($params['pageSize']?$params['pageSize']:10); 

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
                c.`status`,
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
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }
    public function getCarByType($params){
        $where = "WHERE c.is_del = 0 AND c.status = 1 AND c.is_use = 1 AND c.company_id in (" . implode(',',$params['company_ids']) . ") AND c.type =  ".$params['type'];
        $sql = "SELECT 
                c.`id`,
                c.`number`,
                c.`type`,
                c.`vins`,
                d.`id` as driver_id,
                d.`name` as driver_name,
                d.`mobile` as driver_mobile,
                d2.`name` as escort_name,
                d2.`mobile` as escort_mobile,
                d2.`id` as escort_id
                FROM `gl_cars` AS c
                LEFT JOIN `gl_driver` AS d ON d.`id` = c.`driver_id`
                LEFT JOIN `gl_driver` AS d2 ON d2.`id` = c.`escort_id`
                {$where} 
                ORDER BY c.`updated_at` DESC";
        $result = $this->dbh->select($sql);
        return $result;
    }
    public function add($params)
    {
        
        if( isset($params['file']) ){
            $file = $params['file'];
            unset($params['file']);
        }
        $res = $this->dbh->insert('gl_cars', $params);
        if( $res ){
            if( count($file) ){
                foreach ($file as $key => $value) {
                    $value['cars_id'] = $res;
                    $this->dbh->insert('gl_cars_pic', $value );
                }
            }
            return $res;
        }
        return false;
    }

    public function update($params, $id)
    {
        if( isset($params['file']) ){
            $file = $params['file'];
            unset($params['file']);
        }
        $res = $this->dbh->update('gl_cars', $params, 'id ='.$id);
        if( $res ){
            if( count($file) ){
                $re = $this->dbh->update('gl_cars_pic', array('is_del'=>1), 'cars_id ='.$id);
                foreach ($file as $key => $value) {
                    $value['cars_id'] = $id;
                    $this->dbh->insert('gl_cars_pic', $value );
                }
            }
            return $res;
        }
        return false;
    }

    public function updateMutyStatus($params, $where)
    {
        $res = $this->dbh->update('gl_cars', $params, $where);
        return $res;
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
    public function checkNumber($number = 0)
    {
        $sql = "SELECT * FROM gl_cars WHERE number = ".$number;
        return $this->dbh->select_row($sql);
    }
    //获取文件
    public function getFileByType($id, $type)
    {
        $sql = "SELECT * FROM gl_cars_pic WHERE `is_del` = 0 AND cars_id = {$id} AND type = {$type} ";
        if( $type == 3 ){
            return $this->dbh->select($sql);
        }else{
            return $this->dbh->select_row($sql);
        }
        
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
        $filter_r[] = " WHERE r.`is_del` = 0 AND r.`status` = 1 ";//回程车
        $filter_z[] = " WHERE z.`is_del` = 0 AND z.`set_line` = 1 AND z.`is_use` = 1 AND p.`is_del`= 0 " ;//专线车
        $where_r = "  ";
        $where_z = "  ";

        //筛选承运商
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter_r[] = " r.`cid` = " . intval($params['cid']);
            $filter_z[] = " z.`cid` = " . intval($params['cid']);
        }
        if( isset($params['start_province_id']) && $params['start_province_id'] != '' && $params['start_province_id'] != '0'){
            if( isset($params['start_city_id']) && $params['start_city_id'] != '' && $params['start_city_id'] != '0'){
                if( isset($params['start_area_id']) && $params['start_area_id'] != '' && $params['start_area_id'] != '0'){
                    //全县
                    $filter_r[] = "  r.`start_province_id` = '{$params['start_province_id']}'  AND ( r.`start_city_id` = 0 OR r.`start_area_id` = 0 OR r.`start_area_id` = '{$params['start_area_id']}' ) ";
                    $filter_z[] = "  z.`start_province_id` = '{$params['start_province_id']}'  AND (z.`start_city_id` = 0 OR z.`start_area_id` = 0 OR z.`start_area_id` = '{$params['start_area_id']}' ) ";
                }else{
                    //全市
                    $filter_r[] = "  r.`start_province_id` = '{$params['start_province_id']}'  AND ( r.`start_city_id` = 0 OR r.`start_city_id` = '{$params['start_city_id']}' ) ";
                    $filter_z[] = "  z.`start_province_id` = '{$params['start_province_id']}'  AND ( z.`start_city_id` = 0 OR z.`start_city_id` = '{$params['start_city_id']}' ) ";
                }
            }else{
                //全省
                $filter_r[] = "  r.`start_province_id` = '{$params['start_province_id']}'  ";
                $filter_z[] = "  z.`start_province_id` = '{$params['start_province_id']}' ";
            }
        }

        if( isset($params['end_province_id']) && $params['end_province_id'] != '' && $params['end_province_id'] != '0'){
            if( isset($params['end_city_id']) && $params['end_city_id'] != '' && $params['end_city_id'] != '0'){
                if( isset($params['end_area_id']) && $params['end_area_id'] != '' && $params['end_area_id'] != '0'){
                    //全县
                    $filter_r[] = " r.`end_province_id` = '{$params['end_province_id']}' AND ( r.`end_city_id` = 0 OR r.`end_city_id` = '{$params['end_city_id']}' OR r.`end_area_id` = 0 OR r.`end_area_id` = '{$params['end_area_id']}' ) ";
                    $filter_z[] = " z.`end_province_id` = '{$params['end_province_id']}' AND ( z.`end_city_id` = 0 OR z.`end_city_id` = '{$params['end_city_id']}' OR z.`end_area_id` = 0 OR z.`end_area_id` = '{$params['end_area_id']}' ) ";
                }else{
                    //全市
                    $filter_r[] = "  r.`end_province_id` = '{$params['end_province_id']}' AND ( r.`end_city_id` = 0 OR r.`end_city_id` = '{$params['end_city_id']}' ) ";
                    $filter_z[] = "  z.`end_province_id` = '{$params['end_province_id']}' AND ( z.`end_city_id` = 0 OR z.`end_city_id` = '{$params['end_city_id']}' ) ";
                }
            }else{
                //全省
                $filter_r[] = "  r.`end_province_id` = '{$params['end_province_id']}'  ";
                $filter_z[] = "  z.`end_province_id` = '{$params['end_province_id']}'  ";
            }
        }

          //筛选分类
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $filter_r[] = " r.`category_id` = " . intval($params['category_id']);
            $filter_z[] = " p.`category_id` = " . intval($params['category_id']);
        }
        //筛选分类
        if (isset($params['category_id_two']) && !empty($params['category_id_two'])) {
            $filter_r[] = " r.`category_id_two` = " . intval($params['category_id_two']);
            $filter_z[] = " p.`produce_id` = " . intval($params['category_id_two']);
        }
           //筛选产品
        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $filter_r[] = " r.`product_id` = " . intval($params['product_id']);
            $filter_z[] = " p.`product_id` = " . intval($params['product_id']);
        }
        //筛选开始时间
        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter_r[] = " unix_timestamp(r.`start_time`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }
        //筛选结束时间
        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter_r[] = " unix_timestamp(r.`end_time`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        //重量
        if (isset($params['load']) && !empty($params['load'])) {
            $filter_z[] = " z.`max_load` >=  " . intval($params['load']);
            $filter_z[] = " z.`min_load` <=  " . intval($params['load']);

            $filter_r[] = " r.`max_load` >=  " . intval($params['load']);
            $filter_r[] = " r.`min_load` <=  " . intval($params['load']);
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


        $sql = "SELECT COUNT(*) FROM(
                 SELECT z.start_province_id,z.start_city_id,z.id,z.cid,z.car_type,z.price_type,z.price,z.min_load,z.max_load,z.loss,p.product_id,1 AS ctype,com.company_name
                 FROM gl_rule AS z
                 LEFT JOIN gl_rule_product AS p ON p.rule_id = z.id
                 LEFT JOIN gl_companies AS com ON com.id = z.cid {$where_z}
                UNION
                 SELECT r.start_province_id,r.start_city_id,r.id,r.cid,0 AS car_type,r.price_type,r.price,r.min_load,r.max_load,r.loss,r.product_id,2 AS ctype,com.company_name
                 FROM gl_return_car AS r
                 LEFT JOIN gl_companies AS com ON com.id = r.cid {$where_r}
                ) AS ss ";
        $result['totalRow'] = $this->dbh->select_one($sql);
        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT '' as starttime,z.start_province_id,z.start_city_id,z.end_province_id,z.end_city_id,z.id,z.cid,z.car_type,z.price_type,z.price,z.min_load,z.max_load,z.loss,p.product_id,1 AS ctype,com.company_name
                 FROM gl_rule AS z
                 LEFT JOIN gl_rule_product AS p ON p.rule_id = z.id
                 LEFT JOIN gl_companies AS com ON com.id = z.cid {$where_z}
                UNION
                 SELECT r.start_time as starttime,r.start_province_id,r.start_city_id,r.end_province_id,r.end_city_id,r.id,r.cid,0 AS car_type,r.price_type,r.price,r.min_load,r.max_load,r.loss,r.product_id,2 AS ctype,com.company_name
                 FROM gl_return_car AS r
                 LEFT JOIN gl_companies AS com ON com.id = r.cid {$where_r}
                ORDER BY id DESC ";
        $result['list'] = $this->dbh->select_page($sql);
        if( count($result['list']) ){
            foreach ($result['list'] as $k => $v) {

                $type = 'area';
                if( $v['start_area_id'] == 0 ){
                    if( $v['start_city_id'] == 0 ){
                        $type = 'province';
                    }else{
                        $type = 'city';
                    }
                }
                $name = "start_{$type}_id";
                $sql = "SELECT GROUP_CONCAT(cp.`{$type}`) FROM conf_{$type} cp where cp.`{$type}id` = {$v[$name]}";
                $data = $this->dbh->select_one($sql);
                $result['list'][$k]['start_name'] = $data ? $data:'';

                $type = 'area';
                if( $v['end_area_id'] == 0 ){
                    if( $v['end_city_id'] == 0 ){
                        $type = 'province';
                    }else{
                        $type = 'city';
                    }
                }
                $name = "end_{$type}_id";
                $sql = "SELECT GROUP_CONCAT(cp.`{$type}`) FROM conf_{$type} cp where cp.`{$type}id` = {$v[$name]}";
                $data = $this->dbh->select_one($sql);
                $result['list'][$k]['end_name'] = $data ? $data:'';
            }
        }
        return $result;
    }

}