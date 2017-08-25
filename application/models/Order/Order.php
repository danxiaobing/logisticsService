<?php
/**
 * User: Daley
 */
class Order_OrderModel
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

    /**
     * 托运单列表
     * @param $params
     * @return mixed
     */
    public function getOrderList($params)
    {

        $filter = array();

        $where = 'gl_order.is_del = 0 and gl_goods.is_del = 0 ';

        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " gl_goods.`cid` =".$params['cid'];
        }
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


        if (isset($params['cargo_id']) && $params['cargo_id'] != '') {
            $filter[] = " gl_order.`cargo_id` = '{$params['cargo_id']}'";
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " gl_order.`status` = '{$params['status']}'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " gl_order.`created_at` >= '{$params['starttime']}'";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " gl_order.`created_at` >= '{$params['endtime']}'";
        }

        if (isset($params['number']) && $params['number'] != '') {
            $filter[] = " gl_order.`number` LIKE '%" . trim($params['number']) . "%'";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_goods  LEFT JOIN gl_order ON gl_order.goods_id = gl_goods.id  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
               gl_order.id,
               gl_goods.start_provice_id,
               gl_goods.end_provice_id,
               gl_goods.product_id,
               gl_goods.weights,
               gl_goods.price,
               gl_order.number,
               gl_order.status,
               gl_order.created_at,
               gl_products.zh_name as product_name
                FROM gl_goods
                LEFT JOIN gl_order ON gl_order.goods_id = gl_goods.id
                 LEFT JOIN gl_products ON gl_products.id = gl_goods.product_id
                WHERE  {$where}
                ORDER BY id DESC";
        //  print_r($sql);die;
        $result['list']  = $this->dbh->select_page($sql);
        return $result;
    }


}
