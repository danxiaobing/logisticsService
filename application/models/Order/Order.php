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

        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " o.`status` = '{$params['status']}'";
        }

        if (isset($params['orderno']) && !empty($params['orderno'])) {
            $filter[] = " g.`orderno` = '{$params['orderno']}'";
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
                g.companies_name,
                com.company_name as carrier_name,
                g.price,
                g.desc_str,
                o.number,
                o.status,
                o.created_at
                FROM gl_order o
                LEFT JOIN gl_goods g ON o.goods_id = g.id
                LEFT JOIN gl_companies com ON com.id = o.company_id
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
        if(!empty($info)){
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
               god.`is_del` = 0 AND  god.`status` NOT IN('6') AND god.`order_id` = ".intval($orderid);
            $res = $this->dbh->select($sql);
        }

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
            unset($goods_info['carriers_price']);
            unset($goods_info['offer_price']);
            unset($goods_info['stype']);

            //对地址判断
            $Address = new Cargo_AddressModel($this->dbh);

            /**对发货地址进行判断 start**/
            $fahuo_params['cid'] = $params['cid'];
            $fahuo_params['provice_id'] = $params['start_provice_id'];
            $fahuo_params['city_id'] = $params['start_city_id'];
            $fahuo_params['area_id'] = $params['start_area_id'];
            $fahuo_params['address'] = $params['off_address'];
            $fahuo_params['type'] = 2;
            $res = $Address->getCargoAddres($fahuo_params);
            if(!$res){

                /* 发货地址新增*/
                $fahuo_params['uid'] = $params['uid'];
                $fahuo_params['name'] = $params['off_user'];
                $fahuo_params['mobile'] =$params['off_phone'];
                $fahuo_params['remark'] ='';
                /*   $fahuo_params['test'] = 1;*/
                $Address->addCargoAddress($fahuo_params);

            }else{

                /*常用地址加一*/
                /* $sql ="update gl_cargo_address set test= test+1 WHERE id=".$res['id'];
                $this->dbh->exe($sql);*/
            }

            /**对卸货地址进行判断 start**/

            $xiehuo_params['cid'] = $params['cid'];
            $xiehuo_params['provice_id'] = $params['end_provice_id'];
            $xiehuo_params['city_id'] = $params['end_city_id'];
            $xiehuo_params['area_id'] = $params['end_area_id'];
            $xiehuo_params['address'] = $params['reach_address'];

            $xiehuo_params['type'] = 1;
            $res = $Address->getCargoAddres($xiehuo_params);
            if(!$res){

                /* 发货地址新增*/
                $xiehuo_params['uid'] = $params['uid'];
                $xiehuo_params['name'] = $params['reach_user'];
                $xiehuo_params['mobile'] =$params['reach_phone'];
                $xiehuo_params['remark'] = '';
                /*  $xiehuo_params['test'] =1;*/
                $Address->addCargoAddress($xiehuo_params);

            }else{

                /*常用地址加一*/
                /*  $sql ="update gl_cargo_address set test= test+1 WHERE id=".$res['id'];
                $this->dbh->exe($sql);*/
            }


            /**对联系人进行判断 start**/
            $lianxiren_params['cid'] = $params['cid'];
            $lianxiren_params['name'] = $params['consign_user'];
            $lianxiren_params['mobile'] = $params['consign_phone'];
            $lianxiren_params['type'] = 3;
            $res = $Address->getCargoAddres($lianxiren_params);
            if(!$res){

                /* 发货地址新增*/
                $lianxiren_params['uid'] = $params['uid'];
                $lianxiren_params['remark'] = '';
                /*  $lianxiren_params['test'] = 1;*/
                $Address->addCargoAddress($lianxiren_params);

            }else{

                /*常用地址加一*/
                /*  $sql ="update gl_cargo_address set test= test+1 WHERE id=".$res['id'];
                $this->dbh->exe($sql);*/
            }
            //对地址判断
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


    /* 合同信息
     * 获取单个托运单的详情
    */
    public function getOrderInfo($orderid)
    {
        //获取托运单基本信息
        $sql = "SELECT go.id,go.number,go.cargo_id,go.company_id,go.goods_id,go.estimate_freight,go.status,go.fact_freight,gl_in.price as inquiryprice
          FROM gl_order go
          LEFT JOIN gl_inquiry gl_in ON  gl_in.order_id=go.id
          WHERE go.id=" . intval($orderid);

        $orderinfo = $this->dbh->select_row($sql);

        //获取goods基本信息
        $sql = "SELECT
                     gd.id,
                     gd.cid,
                     gd.start_provice_id,
                     gd.start_city_id,
                     gd.start_area_id,
                     gd.end_provice_id,
                     gd.end_city_id ,
                     gd.end_area_id ,
                     gd.product_id,
                     gd.weights,
                     gd.weights_done,
                     gd.price ,
                     gd.companies_name ,
                     gd.off_starttime ,
                     gd.off_endtime ,
                     gd.reach_starttime ,
                     gd.reach_endtime ,
                     gd.offer_status,
                     gd.offer_price,
                     gd.loss,
                     gd.pay_type,
                     gd.qq,
                     gd.desc_str ,
                     gd.off_address,
                     gd.off_user ,
                     gd.off_phone ,
                     gd.reach_address ,
                     gd.reach_user ,
                     gd.reach_phone ,
                     gd.consign_user ,
                     gd.consign_phone,
                     gct.`name`,
                     gd.created_at,
                     gd.`status`
                     FROM gl_goods gd
                     LEFT JOIN gl_cars_type gct ON  gct.id=gd.cars_type WHERE gd.id =" . $orderinfo['goods_id'];
        $goodsdata = $this->dbh->select_row($sql);
        if (!empty($orderinfo)) {
            #sql语句
            $sql = 'SELECT
                  gl_companies.id,
                  gl_companies.bank_name,
                  gl_companies.bank_num,
                  gl_companies.company_name
                  FROM gl_companies WHERE
                  gl_companies.id = ' . intval($orderinfo['company_id']);
            $carrier_data = $this->dbh->select_row($sql);
        }
        //获取市的信息
        $city = $this->dbh->select('SELECT cityid,city FROM conf_city');

        return array('orderinfo' => $orderinfo, 'goodsdata' => $goodsdata, 'carrier_data' => $carrier_data, 'city' => $city);
    }



}
