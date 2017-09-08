<?php
/**
 * 待办事项
 * User: Daley
 */
class TodoModel
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

    //承运商待办事项
    public function carrierTodo($params)
    {
        $result['inquiry_dwbj']  = 0;
        $result['inquiry_ddfbj'] = 0;
        $result['consigns_yyz']  = 0;
        $result['consigns_dzf']  = 0;
        $result['consigns_bth']  = 0;
        $result['dispatch_a']  = 0;
        $result['dispatch_b']  = 0;
        $result['dispatch_c']  = 0;
        $result['driver_a']  = 0;
        $result['driver_b']  = 0;
        $result['cars']  = 0;
        $result['companies']  = 0;
        $result['return_car']  = 0;

        if (isset($params['cid']) && $params['cid'] != '') {

           //询价单待我报价
            $sql = "SELECT count(1) FROM `gl_inquiry` WHERE is_del = 0  and  status= 1 AND cid = {$params['cid']}";
            $result['inquiry_dwbj'] = $this->dbh->select_one($sql);

            //询价单待货主报价
            $sql = "SELECT count(1) FROM `gl_inquiry` WHERE is_del = 0  AND status= 2 AND cid = {$params['cid']}";
            $result['inquiry_dfbj'] = $this->dbh->select_one($sql);



            //<--托运单start-->

            //托运单预约中--等待调度
            $where =" gl_order.is_del = 0 and gl_goods.is_del = 0  AND gl_order.`company_id` = {$params['cid']}";

            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 1";
            $result['consigns_yyz'] = $this->dbh->select_one($sql);


           //待支付(待支付)
            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 4";
            $result['consigns_dzf'] = $this->dbh->select_one($sql);


            //被退回(修改托运单)等待货主托运单
            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 7";
            $result['consigns_bth'] = $this->dbh->select_one($sql);

            //<--托运单end-->

            //<---调度单start-->
            //待确认发车
            $sql = "SELECT count(1) FROM gl_order_dispatch WHERE is_del = 0 AND  status= 0 AND c_id = {$params['cid']}";
            $result['dispatch_a'] = $this->dbh->select_one($sql);
             //待确认提货
            $sql = "SELECT count(1) FROM gl_order_dispatch WHERE is_del = 0 AND  status = 2 AND c_id = {$params['cid']}";
            $result['dispatch_b'] = $this->dbh->select_one($sql);
             //待确认卸货
            $sql = "SELECT count(1) FROM gl_order_dispatch WHERE is_del = 0 AND  status= 4 AND c_id = {$params['cid']}";
            $result['dispatch_c'] = $this->dbh->select_one($sql);

              //<---调度单end-->

            //司机信息备案中

            if (isset($params['company_ids']) && count($params['company_ids']) ) {
                $where = "AND  `company_id` in (" . implode(',', $params['company_ids']) . ")";

                $sql = "SELECT count(1) FROM gl_driver WHERE isdelete = 0 AND type=1 AND status= 0 {$where}";
                $result['driver_a'] = $this->dbh->select_one($sql);
            }
            //押运员信息备案中
            $sql = "SELECT count(1) FROM gl_driver WHERE isdelete = 0 AND type= 2 AND status= 0 AND company_id = {$params['cid']}";
            $result['driver_b'] = $this->dbh->select_one($sql);

            //车辆信息备案中

            if (isset($params['company_ids']) && count($params['company_ids']) ) {
                $where = "AND  `company_id` in (".implode(',',$params['company_ids']).")";

                $sql = "SELECT count(1) FROM gl_cars WHERE is_del = 0  AND status= 0 {$where}";
                $result['cars'] = $this->dbh->select_one($sql);
            }


              //承运商信息备案中
            $sql = "SELECT count(1) FROM gl_companies WHERE is_del = 0  AND status= 1 AND pid = {$params['cid']}";
            $result['companies'] = $this->dbh->select_one($sql);

            //<--回程车-->
            //回程车待接单
            $sql = "SELECT count(1) FROM gl_return_car  WHERE  gl_return_car.`is_del` = 0 AND gl_return_car.`status` = 1";
            $result['return_car'] = $this->dbh->select_one($sql);
            //<--回程车-->


        }

        return $result;
    }







    //货主待办事项
    public function cargoTodo($params)
    {
        $result['goods_djd']  = 0;
        $result['inquiry_dwbj'] = 0;
        $result['inquiry_dfbj'] = 0;
        $result['consigns_yyz']  = 0;
        $result['consigns_yfp']  = 0;
        $result['consigns_jxz']  = 0;
        $result['consigns_dzf']  = 0;
        $result['consigns_bth']  = 0;

        if (isset($params['cid']) && $params['cid'] != '') {
            //货源待接单
            $sql = "SELECT count(1) FROM `gl_goods` WHERE gl_goods.`is_del` = 0 AND gl_goods.`source` = 0 AND gl_goods.`status`= 1 AND gl_goods.`cid` = {$params['cid']}";
            $result['goods_djd'] = $this->dbh->select_one($sql);


             //询价单待我报价
            $sql = "SELECT count(1)
                           FROM `gl_inquiry`
                           LEFT JOIN gl_goods ON gl_goods.id = gl_inquiry.gid
                           WHERE gl_inquiry.`is_del` = 0  and gl_goods.is_del = 0 AND gl_inquiry.`status`= 2 AND gl_goods.`cid` = {$params['cid']}";

            $result['inquiry_dwbj'] = $this->dbh->select_one($sql);


            //询价单待对方报价

            $sql = "SELECT count(1)
                           FROM `gl_inquiry`
                           LEFT JOIN gl_goods ON gl_goods.id = gl_inquiry.gid
                           WHERE gl_inquiry.`is_del` = 0  and gl_goods.is_del = 0 AND gl_inquiry.`status`= 1 AND gl_goods.`cid` = {$params['cid']}";

            $result['inquiry_dfbj'] = $this->dbh->select_one($sql);

            //托运单预约中
            $where =" gl_order.is_del = 0 and gl_goods.is_del = 0  AND gl_order.`cargo_id` = {$params['cid']}";

            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 1";
            $result['consigns_yyz'] = $this->dbh->select_one($sql);


           //托运单已分配调度(待发车)

            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 2";
            $result['consigns_yfp'] = $this->dbh->select_one($sql);


            //托运单进行中
            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 3";
            $result['consigns_jxz'] = $this->dbh->select_one($sql);


           //待支付(待支付)
            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 4";
            $result['consigns_dzf'] = $this->dbh->select_one($sql);


            //被退回(修改托运单)
            $sql = "SELECT count(1) FROM gl_order LEFT JOIN gl_goods ON gl_order.goods_id = gl_goods.id WHERE {$where} AND gl_order.`status` = 7";
            $result['consigns_bth'] = $this->dbh->select_one($sql);
        }

        return $result;
    }


}
