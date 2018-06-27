<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 14:28
 */
class App_Carrier_OrderModel
{
    public $dbh  = null;
    public $dbh2 = null;
    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null, $dbh2)
    {
        $this->dbh = $dbh;
        $this->dbh2 = $dbh2;
    }

    public function getList($params)
    {
        $where = 'o.is_del = 0 and g.is_del = 0 ';
        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);
        $sql = "SELECT
                o.id as order_id,
                g.start_provice_id,
                g.end_provice_id,
                g.product_id,
                g.weights,
                g.off_address,
                g.reach_address,
                g.companies_name,
                com.company_name,
                g.price,
                g.desc_str,
                o.number as order_number,
                o.status,
                o.created_at,
                car.name as cars_type_name,
                god.dispatch_number as cars_type_name
                FROM gl_order o
                LEFT JOIN gl_goods g ON o.goods_id = g.id
                LEFT JOIN gl_companies com ON com.id = o.company_id
                LEFT JOIN gl_cars_type car ON car.id = g.cars_type
                LEFT JOIN gl_order_dispatch god ON o.id = god.order_id
                WHERE  {$where}
                ORDER BY order_id DESC";
        $result['list']  = $this->dbh->select_page($sql);
        foreach ($result['list'] as $k => &$v) {
            $v['unit'] = '吨';
            $sql = "SELECT title FROM td_category_goods WHERE td_category_goods.id=".$v['product_id'];
            $product_name = $this->dbh2->select_one($sql);
            $v['product_name'] = $product_name;
        }
        return $result;
    }

    public function getDetail($orderid = 0)
    {
        $sql = "SELECT  o.id,
                        o.goods_id,
                        o.cargo_id,                       
                        o.number as order_number,          
                        o.cargo_id,          
                        g.weights,
                        g.off_address,   
                        g.reach_address,   
                        g.loss,   
                        g.off_user,   
                        g.off_phone,   
                        g.reach_user,   
                        g.reach_phone,   
                        g.product_id,   
                        od.dispatch_number,
                        od.cars_number,
                        od.start_time,
                        od.end_time,
                        cp1.province as start_provice,
                        cp2.province as end_provice,
                        cc1.city as start_city,
                        cc2.city as end_city,
                        ca1.area as start_area,
                        ca2.area as end_area
                        FROM gl_order o
                        LEFT JOIN gl_goods g ON o.goods_id = g.id
                        LEFT JOIN gl_order_dispatch od ON o.id = od.order_id
                        LEFT JOIN conf_province cp1 ON cp1.provinceid = g.start_provice_id
                        LEFT JOIN conf_province cp2 ON cp2.provinceid = g.end_provice_id
                        LEFT JOIN conf_city cc1 ON cc1.cityid = g.start_city_id
                        LEFT JOIN conf_city cc2 ON cc2.cityid = g.end_city_id
                        LEFT JOIN conf_area ca1 ON ca1.areaid = g.start_area_id
                        LEFT JOIN conf_area ca2 ON ca2.areaid = g.end_area_id
                        WHERE
                        o.id = ".intval($orderid);
        $res = $this->dbh->select_row($sql);
        $res['unit'] = '吨';
        $sql = "SELECT IFNULL(company_name,'') name  FROM td_companies WHERE id=".$res['cargo_id'];
        $name = $this->dbh2->select_one($sql);
        $sql = "SELECT title FROM td_category_goods WHERE td_category_goods.id=".$res['product_id'];
        $product_name = $this->dbh2->select_one($sql);
        $res['product_name'] = $product_name;
        $res['cargoname'] = $name;
        return $res;
    }

}