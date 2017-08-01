<?php

/**
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/11 0011
 * Time: 18:32
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


    public function getPage($params, $rows, $page)
    {
        $filed = array();
        $where = " WHERE tcs.`status`=3 ";

        if (isset($params['shopname']) && $params['shopname'] != '') {
            $filed[] = " tcs.`title` LIKE '%" . trim($params['shopname']) . "%'";
        }
        if (count($filed) > 0) {
            $where .= " AND" . implode(" AND ", $filed);
        }

        $result = array(
            'total' => 0,
            'data' => array()
        );

        $sql = "SELECT count(tcs.`id`)
                FROM `td_companies_shop` AS tcs
                LEFT JOIN `td_companies` AS tc ON tc.`id`=tcs.`companiesid` AND tc.`delete`=0
                {$where}";
        $result['total'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($page ? $page : 1);
        $this->dbh->set_page_rows($rows ? $rows : 15);

        $sql = "SELECT tcs.*,tc.`companyno`,tc.`company_name`,tc.`company_telephone`,tc.`organization_code`,tc.`company_address`,tc.`level`
                FROM `td_companies_shop` AS tcs
                LEFT JOIN `td_companies` AS tc ON tc.`id`=tcs.`companiesid` AND tc.`delete`=0
                {$where} ORDER BY tcs.`shoplevel` DESC, tcs.`lastupdated_at` DESC";

        //error_log($sql."\n", 3, 'print_log.txt');
        $result['data'] = $this->dbh->select_page($sql);

        return $result;
    }


    public function Update($params, $id)
    {
        return $this->dbh->update('td_companies_shop_pro', $params, 'shop_id=' . intval($id));
    }


}