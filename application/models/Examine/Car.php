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
        $where = "  ";

        if (isset($params['number']) && $params['number'] != '') {
            $filed[] = " gc.`number` LIKE '%" . trim($params['number']) . "%'";
        }
        if (isset($params['vins']) && $params['vins'] != '') {
            $filed[] = " gc.`vins` LIKE '%" . trim($params['vins']) . "%'";
        }
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filed[] = " cc.`company_name` LIKE '%" . trim($params['company_name']) . "%'";
        }
        if (count($filed) > 0) {
            $where .= " WHERE " . implode(" AND ", $filed);
        }

        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(gc.`id`)
                FROM `gl_cars` AS gc
                LEFT JOIN `gl_companies` AS cc ON cc.`id`=gc.`company_id`
                {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT gc.* , cc.`id`,cc.`company_name`
                FROM `gl_cars` AS gc
                LEFT JOIN `gl_companies` AS cc ON cc.`id`=gc.`company_id`
                {$where} 
                ORDER BY gc.`updated_at` DESC";
        //
        $result['list'] = $this->dbh->select_page($sql);
        //print_r($result);die;
        return $result;
    }


    public function Update($params, $id)
    {
        return $this->dbh->update('td_companies_shop_pro', $params, 'shop_id=' . intval($id));
    }


}