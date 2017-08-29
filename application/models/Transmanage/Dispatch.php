<?php

/**
 * 询价单管理
 * User: Jeff
 */
class Transmanage_DispatchModel
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

        if (isset($params['end_provice_id']) && $params['end_provice_id'] != '') {
            $filter[] = " g.`end_provice_id` =".$params['end_provice_id'];
        }

        if (isset($params['end_city_id']) && $params['end_city_id'] != '') {
            $filter[] = " g.`end_city_id` =".$params['end_city_id'];
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

        $where = ' o.`is_del` = 0 ';

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT count(1) FROM gl_order AS o LEFT JOIN gl_goods AS g ON g.`id` = o.`goods_id`  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

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

        $result['list'] = $this->dbh->select_page($sql);

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
}