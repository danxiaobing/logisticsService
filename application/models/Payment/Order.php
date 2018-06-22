<?php

/**
 * 收付款单-付款单关联订单表
 */
class Payment_OrderModel
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
     * 获得订单的所有信息
     */
    public function getList($params){
        $filter = array();

        $where = ' g.isdel = 0 ';

        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " g.`c_id` =".$params['cid'];
        }


        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " g.`status` = '{$params['status']}'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " unix_timestamp(g.`created_at`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " unix_timestamp(g.`created_at`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM payment_order g  WHERE {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);
        // print_r($filter);die;

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT g.*
                FROM payment_order g
                WHERE  {$where}
                ORDER BY g.id DESC";
        $result['list']  = $this->dbh->select_page($sql);
        return $result;
    }



    /**
     * 获取货源询价单详情
     * @param $id
     * @return mixed
     */
    public function getInfo($id){

        //查询询价单信息
        $sql = "SELECT *
               FROM payment_order i
               WHERE i.isdel = 0 AND i.id=".$id." ORDER BY id DESC";
        $result['inquiry'] = $this->dbh->select_row($sql);

       //询价单记录信息
        // $sql = "SELECT id,minprice,maxprice,cid,type,updated_at,created_at
        //         FROM gl_inquiry_info WHERE is_del = 0 AND pid=".$id." ORDER BY id ASC";
        // $result['inquiry_info'] = $this->dbh->select($sql);
        return $result;
    }

}