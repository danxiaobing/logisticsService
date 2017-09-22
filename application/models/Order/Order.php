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

        $where = 'o.is_del = 0 and g.is_del = 0 ';

        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " g.`cid` =".$params['cid'];
        }
         /*     if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " o.`cargo_id` = {$params['cid']}";
        }*/
        if (isset($params['uid']) && !empty($params['uid'])) {
            $filter[] = " g.`uid` =".$params['uid'];
        }
        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " g.`start_provice_id` =".$params['start_provice_id'];
        }

        if (isset($params['start_city_id']) && $params['start_city_id'] != '') {
            $filter[] = " g.`start_city_id` =".$params['start_city_id'];
        }

        if (isset($params['start_area_id']) && $params['start_area_id'] != '') {
            $filter[] = " g.`start_area_id` =".$params['start_area_id'];
        }

        if (isset($params['end_provice_id']) && $params['end_provice_id'] != '') {
            $filter[] = " g.`end_provice_id` =".$params['end_provice_id'];
        }

        if (isset($params['end_city_id']) && $params['end_city_id'] != '') {
            $filter[] = " g.`end_city_id` =".$params['end_city_id'];
        }

        if (isset($params['end_area_id']) && $params['end_area_id'] != '') {
            $filter[] = " g.`end_area_id` =".$params['end_area_id'];
        }

        if (isset($params['start_weights']) && $params['start_weights'] != '') {
            $filter[] = " g.`weights` >= ".intval($params['start_weights']);
        }

        if (isset($params['end_weights']) && $params['end_weights'] != '') {
            $filter[] = " g.`weights` <= ".intval($params['end_weights']);
        }
        if (isset($params['start_weights']) && $params['start_weights'] != ''&& isset($params['end_weights']) && $params['end_weights'] != '') {
            if($params['start_weights']>$params['end_weights']){
                $filter[] = " g.`weights` >= ".intval($params['end_weights']);
                $filter[] = " g.`weights` <= ".intval($params['start_weights']);
            }
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " o.`status` = '{$params['status']}'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " unix_timestamp(o.`created_at`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " unix_timestamp(o.`created_at`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        if (isset($params['number']) && $params['number'] != '') {
            $filter[] = " o.`number` LIKE '%" . trim($params['number']) . "%'";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_order  o LEFT JOIN gl_goods g ON o.goods_id = g.id  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
                o.id,
                g.start_provice_id,
                g.end_provice_id,
                g.product_id,
                g.weights,
                g.price,
                o.number,
                o.status,
                o.created_at,
                gl_products.zh_name as product_name
                FROM gl_order o
                LEFT JOIN gl_goods g ON o.goods_id = g.id
                 LEFT JOIN gl_products ON gl_products.id = g.product_id
                WHERE  {$where}
                ORDER BY id DESC";
        $result['list']  = $this->dbh->select_page($sql);
        return $result;
    }
    /**
     * 根据id获取详情
     * id: 权限id
     * @return 数组
     */
    public function getInfo($orderid = 0)
    {

        //获取托运单基本信息
        $sql = "SELECT go.id,
                        go.number,
                        go.goods_id,
                        go.cargo_id,
                        go.company_id,
                        go.status,
                        go.reasons,
                        go.estimate_freight,
                        go.fact_freight,
                        go.pay_time
                     FROM gl_order go WHERE go.id=".intval($orderid);
        $info = $this->dbh->select_row($sql);

        //获取托运单的调度信息
        $sql = "SELECT
                god.`id`,
                god.`cars_number` ,
                god.`driver_name` ,
                gd.`mobile` ,
                god.`weights` ,
                god.`start_weights` ,
                god.`end_weights` ,
                god.`status`
              FROM
                gl_order_dispatch god
              LEFT JOIN
                gl_driver gd
              ON
                gd.id = god.driver_id
              WHERE
               god.`is_del` = 0 AND god.`order_id` = ".intval($orderid);
        $res = $this->dbh->select($sql);
        $Schedule =  $res ? $res:[];
        return array('order_info'=>$info,'schedule'=>$Schedule);
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

    /**
     * 货主找车直接生成托运单
     */
    public function addPublishAndCreateOrder($params){

        //1 添加货源信息 2 生成托运单 3.如果是回程车修改回程车状态


        //开始事物
        $this->dbh->begin();
        try{

            $goods_info  = $params;
            unset($goods_info['number']);
            unset($goods_info['car_id']);
            unset($goods_info['carriers_id']);
            unset($goods_info['carriers_price']);
            unset($goods_info['offer_price']);
            unset($goods_info['stype']);

            $gid = $this->dbh->insert('gl_goods',$goods_info);
            if(!$gid){
                $this->dbh->rollback();
                return false;
            }
            $insertInfo = array(
                'number'=> $params['number'],//托运单号
                'cargo_id'=> $params['cid'],//货主id
                'goods_id'=>$gid,
                'company_id'=>$params['carriers_id'],//承运商id
                'estimate_freight'=>$params['carriers_price']*$params['weights'],
                'created_at'=>'=NOW()',
                'updated_at'=>'=NOW()',
            );

            if (isset($params['car_id']) && !empty($params['car_id'])) {
                $insertInfo['car_id'] = $params['car_id'];
            }
            $order = $this->dbh->insert('gl_order',$insertInfo);
            if(!$order){
                $this->dbh->rollback();
                return false;
            }
            if (isset($params['car_id']) && !empty($params['car_id'])) {
                //修改回程车信息状态
                $info['status']  = 6;//已生成托运单
                $info['order_id']  = $order;//已生成托运单
                $result = $this->dbh->update('gl_return_car',$info,'id ='.$params['car_id']);
                if(!$result){
                    $this->dbh->rollback();
                    return false;
                }
            }

            $this->dbh->commit();
            return $order;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }



    }





}
