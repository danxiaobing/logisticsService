<?php
/**
 * User: Daley
 */
use Hprose\Client;
class Cargo_GoodsModel
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
        $msg = Yaf_Application::app()->getConfig()->get("smsrpc");
        $this->Msg  =  Client::create($msg->host.'Wiserun',false);
    }

    //列表
    public function getList($params)
    {
        $filter = $filed = array();
        $where = 'WHERE g.is_del = 0 AND g.source = 0 ';
        $order = "g.updated_at";

        if (isset($params['order']) && $params['order'] != '') {
            if($params['order'] == 'o_s'){
                $order = 'g.off_starttime';
            }
            if($params['order'] == 'r_s'){
                $order = 'g.reach_starttime';
            }
        }
        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " g.cid = " . intval($params['cid']);
        }
        if (isset($params['uid']) && !empty($params['uid'])) {
            $filter[] = " g.uid = " . intval($params['uid']);
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " g.status =" . intval($params['status']);
        }
        if (isset($params['orderno']) && !empty($params['orderno'])) {
            $filter[] = " g.orderno ='" . $params['orderno']."'";
        }
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1) FROM `gl_goods` g {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
               g.id,
               g.start_provice_id,
               g.start_city_id,
               g.start_area_id,
               g.end_provice_id,
               g.end_city_id,
               g.end_area_id,
               g.start_provice,
               g.start_city,
               g.start_area,
               g.end_provice,
               g.end_city,
               g.end_area,
               g.cate_id,
               g.cate_id_two,
               g.product_id,
               g.product_name,
               g.weights,
               g.price,
               g.offer_status,
               g.offer_price,
               g.companies_name,
               com.company_name as carrier_name,
               g.off_starttime,
               g.reach_starttime,
               g.desc_str,
               g.cars_type,
               g.cars_type_name,
               g.status
               FROM gl_goods g
               LEFT JOIN gl_companies com ON com.id = g.carriers_id
               " . $where . "   ORDER BY {$order} DESC";
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }
    
     //同步数据列表专用
    public function getGoodsTongbuList($params)
    {

        $where = 'WHERE 1=1';
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1) FROM `gl_goods` g {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
               g.id,
               g.start_provice_id,
               g.start_city_id,
               g.start_area_id,
               g.end_provice_id,
               g.end_city_id,
               g.end_area_id,
               g.start_provice,
               g.start_city,
               g.start_area,
               g.end_provice,
               g.end_city,
               g.end_area,
               g.cate_id,
               g.cate_id_two,
               g.product_id,
               g.product_name,
               g.cars_type,
               g.cars_type_name
               FROM gl_goods g
               LEFT JOIN gl_companies com ON com.id = g.carriers_id
               " . $where . "   ORDER BY id DESC";
        $result['list'] = $this->dbh->select_page($sql);
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
               g.id,g.start_provice_id,g.start_city_id,g.start_area_id,g.end_provice_id,g.end_city_id,g.end_area_id,
               g.start_provice,g.start_city,g.start_area,g.end_provice,g.end_city,g.end_area,g.product_name,g.cars_type_name,
               g.cate_id,g.cate_id_two,g.product_id,g.weights,g.price,g.companies_name,g.off_starttime,g.off_endtime,g.reach_starttime,
               g.reach_endtime,g.cars_type,g.pay_type,g.qq,g.loss,g.offer_status,g.carriers_id,g.offer_price,g.off_address,g.off_user,
               g.off_phone,g.reach_address,g.reach_user,g.reach_phone,g.consign_user,g.consign_phone,g.desc_str,g.signing_type,g.status
               FROM gl_goods g WHERE g.id=".$id;
        return $this->dbh->select_row($sql);
    }
    //添加
    public function addInfo($params)
    {
        $this->dbh->begin();
        try{
          /*  $Address = new Cargo_AddressModel($this->dbh);*/

            /**对发货地址进行判断 start**/
          /*  $fahuo_params['cid'] = $params['cid'];
            $fahuo_params['provice_id'] = $params['start_provice_id'];
            $fahuo_params['city_id'] = $params['start_city_id'];
            $fahuo_params['area_id'] = $params['start_area_id'];
            $fahuo_params['address'] = $params['off_address'];
            $fahuo_params['type'] = 2;
            $res = $Address->getCargoAddres($fahuo_params);*/
         /*   if(!$res){

            /* 发货地址新增*/
           /* $fahuo_params['uid'] = $params['uid'];
            $fahuo_params['name'] = $params['off_user'];
            $fahuo_params['mobile'] =$params['off_phone'];
            $fahuo_params['remark'] ='';*/
            /*   $fahuo_params['test'] = 1;
            $Address->addCargoAddress($fahuo_params);

            }else{

            /*常用地址加一*/
            /* $sql ="update gl_cargo_address set test= test+1 WHERE id=".$res['id'];
            $this->dbh->exe($sql);
            }*/

            /**对卸货地址进行判断 start**/

          /*  $xiehuo_params['cid'] = $params['cid'];
            $xiehuo_params['provice_id'] = $params['end_provice_id'];
            $xiehuo_params['city_id'] = $params['end_city_id'];
            $xiehuo_params['area_id'] = $params['end_area_id'];
            $xiehuo_params['address'] = $params['reach_address'];

            $xiehuo_params['type'] = 1;
            $res = $Address->getCargoAddres($xiehuo_params);
            if(!$res){

            /* 发货地址新增*/
         /*   $xiehuo_params['uid'] = $params['uid'];
            $xiehuo_params['name'] = $params['reach_user'];
            $xiehuo_params['mobile'] =$params['reach_phone'];
            $xiehuo_params['remark'] = '';*/
            /*  $xiehuo_params['test'] =1;
            $Address->addCargoAddress($xiehuo_params);

            }else{

            /*常用地址加一*/
            /*  $sql ="update gl_cargo_address set test= test+1 WHERE id=".$res['id'];
            $this->dbh->exe($sql);
            }*/


            /**对联系人进行判断 start**/
         /*   $lianxiren_params['cid'] = $params['cid'];
            $lianxiren_params['name'] = $params['consign_user'];
            $lianxiren_params['mobile'] = $params['consign_phone'];
            $lianxiren_params['type'] = 3;
            $res = $Address->getCargoAddres($lianxiren_params);
            if(!$res){*/

            /* 发货地址新增*/
          /*  $lianxiren_params['uid'] = $params['uid'];
            $lianxiren_params['remark'] = '';
            /*  $lianxiren_params['test'] = 1;
            $Address->addCargoAddress($lianxiren_params);

            }else{*/

            /*常用地址加一*/
            /*  $sql ="update gl_cargo_address set test= test+1 WHERE id=".$res['id'];
            $this->dbh->exe($sql);
            }*/

            $goodsres = $this->dbh->insert('gl_goods',$params);
            if(!$goodsres){
                $this->dbh->rollback();
                return false;
            }

            $receiving_params = array(
                'company_id' => $params['cid'],
                'product_id' => $params['product_id'],
                'start_province_id' => $params['start_provice_id'],
                'start_city_id' => $params['start_city_id'],
                'start_area_id' => $params['start_area_id'],
                'end_province_id' => $params['end_provice_id'],
                'end_city_id' => $params['end_city_id'],
                'end_area_id' => $params['end_area_id'],
                'load' => $params['weights'],
                'car_type' => $params['cars_type'],
                'loss' => $params['loss']
            );
            $T = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
            $company_list = $T->matching($receiving_params);
            //生成询价单
            if ($company_list) {
                foreach ($company_list as $k => $v) {
                    //生成询价的

                    $item = array(
                        'gid' => $goodsres,
                        'status' => 1,//1、等待承运商报价 2、等待货主报价 3、已生成托运单 4、询价取消,
                        'cid' => $v['cid'],
                        'created_at' => '=NOW()',
                        'updated_at' => '=NOW()',
                    );
                    $L = new Cargo_InquiryModel(Yaf_Registry::get("db"));
                     $L->addInquiry($item);
                }
            }

            //获取匹配的运输范围的承运商
            $parameter = array(
                'companyid' => $params['cid'],
                'start_area_id' => $params['start_area_id'],
                'end_area_id' => $params['end_area_id'],
            );

            $T = new Transrange_TransModel(Yaf_Registry::get("db"));
            $info =  $T->getTransMatch($parameter);

            if (count($info)) {
                foreach ($info as $k => $val) {
                    $this->Msg->sendFunc(array('mobile' => $val['mobile']));
                }
            }

            $this->dbh->commit();
            return $goodsres;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }
    }

    //货源指定承运商
    public function goodsAppointCarrier($params){

        $this->dbh->begin();
        try{
            $params['status'] = 2;

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

            $goods_id = $this->dbh->insert('gl_goods',$params);
            if(!$goods_id){
                $this->dbh->rollback();
                return false;
            }
            $insertInfo = array(
                'gid'=>$goods_id,
                'cid'=>$params['carriers_id'],//承运商id
                'status'=>1,
                'created_at'=>'=NOW()',
                'updated_at'=>'=NOW()',
            );

            $inquiry = $this->dbh->insert('gl_inquiry',$insertInfo);
            if(!$inquiry){
                $this->dbh->rollback();
                return false;
            }

            $this->dbh->commit();
            return true;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }

    }

    //修改
    public function updata($params,$id)
    {
        $this->dbh->begin();
        try{
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
            $res = $this->dbh->update('gl_goods',$params,'id=' . intval($id));
            if(!$res){
                $this->dbh->rollback();
                return false;
            }
            $this->dbh->commit();
            return true;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }
    }

    //删除
    public function delete($id)
    {
        $data = [
            'is_del' => 1,
            'updated_at' => '=NOW()'
        ];
        return $this->dbh->update('gl_goods',$data,'id=' . intval($id));
    }


    /**
     * 搜索
     * @param array $params
     * @return array
     * @author amor
     */
    public function searchGoods($params){
        $filter = array();
        $where = ' g.`is_del` = 0 AND g.`status` = 1 AND g.source = 0 ';

        if (isset($params['start_provice_id']) && !empty($params['start_provice_id'])) {
            $filter[] = " g.`start_provice_id` =".intval($params['start_provice_id']);
        }

        if (isset($params['start_city_id']) && !empty($params['start_city_id'])) {
            $filter[] = " g.`start_city_id` =".intval($params['start_city_id']);
        }

        if (isset($params['start_area_id']) && !empty($params['start_area_id'])) {
            $filter[] = " g.`start_area_id` =".intval($params['start_area_id']);
        }


        if (isset($params['end_provice_id']) && !empty($params['end_provice_id'])) {
            $filter[] = " g.`end_provice_id` =".intval($params['end_provice_id']);
        }

        if (isset($params['end_city_id']) && !empty($params['end_city_id'])) {
            $filter[] = " g.`end_city_id` =".intval($params['end_city_id']);
        }

        if (isset($params['end_area_id']) && !empty($params['end_area_id'])) {
            $filter[] = " g.`end_area_id` =".intval($params['end_area_id']);
        }
        if (isset($params['off_starttime']) && $params['off_starttime'] != '') {
            $filter[] = " unix_timestamp(g.`off_starttime`) >= unix_timestamp('{$params['off_starttime']} 00:00:00')";
        }
        if (isset($params['off_endtime']) && $params['off_endtime'] != '') {
            $filter[] = " unix_timestamp(g.`off_starttime`) <= unix_timestamp('{$params['off_endtime']} 00:00:00')";
        }
        if (isset($params['reach_starttime']) && $params['reach_starttime'] != '') {
            $filter[] = " unix_timestamp(g.`reach_starttime`) >= unix_timestamp('{$params['reach_starttime']} 00:00:00')";
        }
        if (isset($params['reach_endtime']) && $params['reach_endtime'] != '') {
            $filter[] = " unix_timestamp(g.`reach_starttime`) <= unix_timestamp('{$params['reach_endtime']} 00:00:00')";
        }
        if (isset($params['cate_id']) && !empty($params['cate_id'])) {
            $filter[] = "g.`cate_id`=".intval($params['cate_id']);
        }
        if (isset($params['cate_id_two']) && !empty($params['cate_id_two'])) {
            $filter[] = "g.`cate_id_two`=".intval($params['cate_id_two']);
        }
        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $filter[] = "g.`product_id`=".intval($params['product_id']);
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        /*  add  将所有未接单并且时间已过期的修改为过期状态*/
        $sql = "SELECT g.id,g.reach_starttime,g.status FROM gl_goods g WHERE  g.`is_del` = 0 AND g.`status` = 1 AND g.`source` = 0 AND g.`reach_starttime`!= '0000-00-00 00:00:00'";
        $list= $this->dbh->select($sql);
        foreach ($list as $key=>$value){
            $date = substr($value['reach_starttime'],0,10) .' 23:59:59';
            if(time()>strtotime($date)){
                $this->updata(array('status'=>3),$value['id']);
            }
        }
      

        $sql = "SELECT count(1) FROM gl_goods  g  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT 
               g.id,
               g.start_provice_id,
               g.start_city_id,
               g.start_area_id,
               g.end_provice_id,
               g.end_city_id,
               g.end_area_id,
               g.cate_id,
               g.cate_id_two,
               g.product_id,
               g.weights,
               g.price,
               g.companies_name,
               g.off_starttime,
               g.off_endtime,
               g.reach_starttime,
               g.reach_endtime,
               g.cars_type,
               g.pay_type,
               g.qq,
               g.loss,
               g.off_address,
               g.off_user,
               g.off_phone,
               g.reach_address,
               g.reach_user,
               g.reach_phone,
               g.consign_user,
               g.consign_phone,
               g.desc_str,
               g.status,
               g.start_provice,
               g.start_city,
               g.start_area,
               g.end_provice,
               g.end_city,
               g.end_area,
               g.product_name,
               g.cars_type_name  AS carname
                FROM gl_goods g
                WHERE  {$where}
                ORDER BY id DESC";


        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }




}
