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
            $filter[] = " unix_timestamp(gl_order.`created_at`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " unix_timestamp(gl_order.`created_at`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
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
    /**
     * 根据id获取详情
     * id: 权限id
     * @return 数组
     */
    public function getInfo($id = 0)
    {
        $sql = "SELECT
                id,
                number,
                cargo_id,
                goods_id,
                company_id,
                estimate_freight,
                fact_freight,
                pay_time,
                status,
                reasons
                 FROM gl_order WHERE gl_order.id=".$id;

        return $this->dbh->select_row($sql);
    }
    //添加
    public function addInfo($params)
    {
        return $this->dbh->insert('gl_order',$params);
    }

    //修改
    public function updata($id,$params)
    {
        return $this->dbh->update('gl_order',$params,'id=' . intval($id));
    }


}
