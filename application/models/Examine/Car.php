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
            $filed[] = " c.`number` LIKE '%" .trim($params['keyworks']). "%' OR d.`name` LIKE '%" .trim($params['keyworks']). "%' OR f.`name` LIKE '%" .trim($params['keyworks']). "%'";
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
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }

        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(c.`id`)
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
                c.*,
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

    public function showfile($id)
    {
        $sql = "SELECT * FROM `gl_cars` WHERE id = ".$id;
        $res = $this->dbh->select_row($sql);
        return $res;
    }

    public function update($params, $where)
    {
        return $this->dbh->update('gl_cars', $params, $where );
    }


}