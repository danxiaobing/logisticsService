<?php
/**
 * User: Daley
 */
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
            $filter[] = " g.status=" . intval($params['status']);
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
               g.end_provice_id,
               g.cate_id,
               g.cate_id_two,
               g.product_id,
               g.weights,
               g.price,
               g.offer_status,
               g.offer_price,
               g.companies_name,
               g.off_starttime,
               g.reach_starttime,
               g.status
               FROM gl_goods g
               " . $where . "   ORDER BY {$order} DESC";
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
               g.id,g.start_provice_id,g.start_city_id,g.start_area_id,g.end_provice_id,g.end_city_id,g.end_area_id,g.cate_id,g.cate_id_two,g.product_id,g.weights,g.price,g.companies_name,g.off_starttime,g.off_endtime,g.reach_starttime,
               g.reach_endtime,g.cars_type,g.loss,g.offer_status,g.carriers_id,g.offer_price,g.off_address,g.off_user,g.off_phone,g.reach_address,g.reach_user,g.reach_phone,g.consign_user,g.consign_phone,g.desc_str,g.status,
               gl_cars_type.name AS cars_type_name
               FROM gl_goods g
               LEFT JOIN gl_cars_type ON gl_cars_type.id = g.cars_type WHERE g.id=".$id;

        return $this->dbh->select_row($sql);
    }
    //添加
    public function addInfo($params)
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

            $res = $this->dbh->insert('gl_goods',$params);
            if(!$res){
                $this->dbh->rollback();
                return false;
            }

            $this->dbh->commit();
            return $res;

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

            $goods_id =   $this->addInfo($params);
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
        /*  end */

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
               IFNULL(gl_cars_type.name,'')  AS carname
                FROM gl_goods g
                LEFT JOIN gl_cars_type ON gl_cars_type.id =g.cars_type
                WHERE  {$where}
                ORDER BY id DESC 
                ";


        $result['list'] = $this->dbh->select_page($sql);
        if(!empty($result['list'])){
            $result['list'] = $this->city($result['list']);
        }

        return $result;
    }

    private function city($data){
        $provice = '';
        $city = '';
        $area = '';


        foreach ($data as $value){
            if (strpos($provice, $value['start_provice_id']) === false) {
                $provice .=  "'".$value['start_provice_id']."',";
            }
            if (strpos($provice, $value['end_provice_id']) === false) {
                $provice .=  "'".$value['end_provice_id']."',";
            }
            if (strpos($provice, $value['end_city_id']) === false) {
                $city .=  "'".$value['end_city_id']."',";
            }
            if (strpos($provice, $value['start_city_id']) === false) {
                $city .=  "'".$value['start_city_id']."',";
            }
            if (strpos($provice, $value['start_area_id']) === false) {
                $area .=  "'".$value['start_area_id']."',";
            }
            if (strpos($provice, $value['end_area_id']) === false) {
                $area .=  "'".$value['end_area_id']."',";
            }
        }

        $provice = substr($provice,0,strlen($provice)-1);
        $city    = substr($city,0,strlen($city)-1);
        $area    = substr($area,0,strlen($area)-1);

        $proviceSql = "SELECT provinceid,province FROM conf_province WHERE provinceid in ({$provice})";
        $citySql = "SELECT cityid,city FROM conf_city WHERE cityid in ({$city})";
        $areaSql = "SELECT areaid,area FROM conf_area WHERE areaid in ({$area})";

        $proviceArr = array_column($this->dbh->select($proviceSql),'province','provinceid');
        $cityArr = array_column($this->dbh->select($citySql),'city','cityid');
        $areaArr = array_column($this->dbh->select($areaSql),'area','areaid');

        foreach ($data as $key=>$value){
            $data[$key]['start_provice'] = $proviceArr[$value['start_provice_id']];
            $data[$key]['end_provice'] = $proviceArr[$value['end_provice_id']];
            $data[$key]['start_city'] = $cityArr[$value['start_city_id']];
            $data[$key]['end_city'] = $cityArr[$value['end_city_id']];
            $data[$key]['start_area'] = $areaArr[$value['start_area_id']];
            $data[$key]['end_area'] = $areaArr[$value['end_area_id']];
        }

        return $data;
    }


}
