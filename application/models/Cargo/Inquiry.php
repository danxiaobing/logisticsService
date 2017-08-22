<?php
/**
 * User: Daley
 * date 2017-08-22
 */
class Cargo_InquiryModel
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

    //列表
    public function getGoodsInquiryList($params)
    {

        $filter = array();

        $where = 'gl_inquiry.is_del = 0 and gl_goods.is_del = 0 ';

        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " gl_goods.`start_provice_id` =".$params['start_provice_id'];
        }

        if (isset($params['start_city_id']) && $params['start_city_id'] != '') {
            $filter[] = " gl_goods.`start_city_id` =".$params['start_city_id'];
        }

        if (isset($params['start_area_id']) && $params['start_area_id'] != '') {
            $filter[] = " gl_goods.`start_area_id` =".$params['start_area_id'];
        }

        if (isset($params['end_provice_id']) && $params['end_provice_id'] != '') {
            $filter[] = " gl_goods.`end_provice_id` =".$params['end_provice_id'];
        }

        if (isset($params['end_city_id']) && $params['end_city_id'] != '') {
            $filter[] = " gl_goods.`end_city_id` =".$params['end_city_id'];
        }

        if (isset($params['end_area_id']) && $params['end_area_id'] != '') {
            $filter[] = " gl_goods.`end_area_id` =".$params['end_area_id'];
        }

        if (isset($params['start_weights']) && $params['start_weights'] != '') {
            $filter[] = " gl_goods.`weights` >= ".intval($params['start_weights']);
        }

        if (isset($params['end_weights']) && $params['end_weights'] != '') {
            $filter[] = " gl_goods.`weights` <= ".intval($params['end_weights']);
        }
        if (isset($params['start_weights']) && $params['start_weights'] != ''&& isset($params['end_weights']) && $params['end_weights'] != '') {
            if($params['start_weights']>$params['end_weights']){
                $filter[] = " gl_goods.`weights` >= ".intval($params['end_weights']);
                $filter[] = " gl_goods.`weights` <= ".intval($params['start_weights']);
            }
        }


        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " gl_inquiry.`status` = '{$params['status']}'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " gl_inquiry.`created_at` >= '{$params['starttime']}'";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " gl_inquiry.`created_at` >= '{$params['endtime']}'";
        }


        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_goods  LEFT JOIN gl_inquiry ON gl_inquiry.gid = gl_goods.id  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT 
               gl_goods.id,
               gl_goods.start_provice_id,
               gl_goods.end_provice_id,
               gl_goods.product_id,
               gl_goods.weights,
               gl_goods.price,
               gl_inquiry.status,
               gl_inquiry.created_at,
               gl_products.zh_name as product_name
                FROM gl_goods 
                LEFT JOIN gl_inquiry ON gl_inquiry.gid = gl_goods.id
                 LEFT JOIN gl_products ON gl_products.id = gl_goods.product_id
                WHERE  {$where}
                ORDER BY id DESC";
      //  print_r($sql);die;
        $result['list']  = $this->dbh->select_page($sql);
        return $result;
    }

    /**
     * 获取货源询价单详情
     */

    public function getGoodsInquirInfo(){

     return array();


    }
}
