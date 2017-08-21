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


}