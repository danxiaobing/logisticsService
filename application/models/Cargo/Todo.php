<?php
/**
 * 待办事项
 * User: Daley
 */
class Cargo_TodoModel
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
    public function statistics($params)
    {
        $result['goods_djd']  = 0;
        $result['inquiry_dwbj'] = 0;
        $result['inquiry_dfbj'] = 0;
        $result['consigns_yyz']  = 0;
        $result['consigns_yfp']  = 0;
        $result['consigns_jxz']  = 0;
        $result['consigns_dzf']  = 0;
        $result['consigns_bth']  = 0;
        //货源待接单
        if (isset($params['cid']) && $params['cid'] != '') {
            $result['goods_djd'] = $this->dbh->select_one("SELECT count(1) FROM `gl_goods` WHERE gl_goods.`is_del` = 0 AND gl_goods.`source` = 0 AND gl_goods.`status`= 1 AND gl_goods.`cid` = {$params['cid']}");
        }
          //询价单待我报价
        if (isset($params['cid']) && $params['cid'] != '') {
            $sql = "SELECT count(1)
                           FROM `gl_inquiry`
                           LEFT JOIN gl_goods ON gl_goods.id = gl_inquiry.gid
                           WHERE gl_inquiry.`is_del` = 0  AND gl_inquiry.`status`= 2 AND gl_goods.`cid` = {$params['cid']}";

            $result['inquiry_dwbj'] = $this->dbh->select_one($sql);

        }
        //询价单待对方报价
        if (isset($params['cid']) && $params['cid'] != '') {
            $sql = "SELECT count(1)
                           FROM `gl_inquiry`
                           LEFT JOIN gl_goods ON gl_goods.id = gl_inquiry.gid
                           WHERE gl_inquiry.`is_del` = 0  AND gl_inquiry.`status`= 1 AND gl_goods.`cid` = {$params['cid']}";

            $result['inquiry_dfbj'] = $this->dbh->select_one($sql);
        }
         //托运单预约中
        if (isset($params['cid']) && $params['cid'] != '') {
            $result['consigns_yyz'] = $this->dbh->select_one("SELECT count(1) FROM `gl_order` WHERE gl_order.`is_del` = 0  AND gl_order.`status`= 1 AND gl_order.`cargo_id` = {$params['cid']}");
        }
        //托运单已分配调度(待发车)
        if (isset($params['cid']) && $params['cid'] != '') {
            $result['consigns_yfp'] = $this->dbh->select_one("SELECT count(1) FROM `gl_order` WHERE gl_order.`is_del` = 0  AND gl_order.`status`= 2 AND gl_order.`cargo_id` = {$params['cid']}");
        }
        //托运单进行中
        if (isset($params['cid']) && $params['cid'] != '') {
            $result['consigns_jxz'] = $this->dbh->select_one("SELECT count(1) FROM `gl_order` WHERE gl_order.`is_del` = 0  AND gl_order.`status`= 3 AND gl_order.`cargo_id` = {$params['cid']}");
        }
        //待支付(待支付)
        if (isset($params['cid']) && $params['cid'] != '') {
            $result['consigns_dzf'] = $this->dbh->select_one("SELECT count(1) FROM `gl_order` WHERE gl_order.`is_del` = 0  AND gl_order.`status`= 4 AND gl_order.`cargo_id` = {$params['cid']}");
        }
         //被退回(修改托运单)
        if (isset($params['cid']) && $params['cid'] != '') {
            $result['consigns_bth'] = $this->dbh->select_one("SELECT count(1) FROM `gl_order` WHERE gl_order.`is_del` = 0  AND gl_order.`status`= 7 AND gl_order.`cargo_id` = {$params['cid']}");
        }

        return $result;
    }


}
