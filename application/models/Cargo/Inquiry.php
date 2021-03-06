<?php
/**
 * User: Daley
 * date 2017-08-22
 */
class Cargo_InquiryModel
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
     * 货源询价单列表
     * @param $params
     * @return mixed
     */
    public function getGoodsInquiryList($params)
    {

        $filter = array();

        $where = 'i.is_del = 0 and g.is_del = 0 ';

        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " g.`start_provice_id` =".$params['start_provice_id'];
        }
        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " g.`cid` =".$params['cid'];
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
            $filter[] = " i.`status` = '{$params['status']}'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " unix_timestamp(i.`created_at`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " unix_timestamp(i.`created_at`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_goods  g LEFT JOIN gl_inquiry i ON i.gid = g.id  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT i.id,g.start_provice,
               g.start_city,
               g.start_area,
               g.end_provice,
               g.end_city,
               g.end_area,g.product_name,g.weights,g.price,i.order_id,i.status,i.created_at
                FROM gl_goods g
                LEFT JOIN gl_inquiry i ON i.gid = g.id
                WHERE  {$where}
                ORDER BY id DESC";
        $result['list']  = $this->dbh->select_page($sql);
        return $result;
    }



    /**
     * 获取货源询价单详情
     * @param $id
     * @return mixed
     */
    public function getGoodsInquiryInfo($id){

        //查询询价单信息
        $sql = "SELECT i.id,i.gid,i.price,i.type,i.status,i.cid,i.order_id,
               gl_goods.consign_user,
               gl_goods.consign_phone
               FROM gl_inquiry i
               LEFT JOIN gl_goods ON gl_goods.id = i.gid
               WHERE i.is_del = 0 AND i.id=".$id." ORDER BY id DESC";
        $result['inquiry'] = $this->dbh->select_row($sql);

       //询价单记录信息
        $sql = "SELECT id,minprice,maxprice,cid,type,updated_at,created_at
                FROM gl_inquiry_info WHERE is_del = 0 AND pid=".$id." ORDER BY id ASC";
        $result['inquiry_info'] = $this->dbh->select($sql);
        return $result;
    }
    /**
     * 生成询价单
     * @param $params
     * @return mixed
     */
    public function addInquiry($params)
    {
        return $this->dbh->insert('gl_inquiry',$params);
    }

    /**
     * 添加货源询价单记录
     * @param $params
     * @return mixed
     */
    public function addInquiryInfo($params)
    {
        return $this->dbh->insert('gl_inquiry_info',$params);
    }

    /**
     * 修改询价单信息
     * @param $params
     * @param $id
     * @return mixed
     */
    public function updataInquiry($id,$params)
    {
        return $this->dbh->update('gl_inquiry',$params,'id=' . intval($id));
    }
    /**
     * 货主报价
     */
    public function goodsInquiryOffer($id,$params){

        if(empty($params)) {
            return false;
        }
        if(empty($id)) {
            return false;
        }
        $this->dbh->begin();
        try{
            $result =  $this->dbh->insert('gl_inquiry_info',$params);

            if(!$result){
                $this->dbh->rollback();
                return false;
            }
            $inquiry['status']  = 1;
            $data = $this->dbh->update('gl_inquiry',$inquiry,'id ='.$id);
            if(empty($data)){
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
    /**
     * 货主同意报价
     * $id 询价单id
     * $params 托运单信息
     */
    public function agreeOffer($id){

        if (empty($id)) {
            return false;
        }

        //获取询价金额
        $where = " info.`type` = 1 AND info.`is_del` = 0 AND gl_inquiry.`id` = {$id}";
        $sql = "SELECT info.`id`,info.`minprice`
                FROM gl_inquiry_info AS info
                LEFT JOIN gl_inquiry ON info.`pid` = gl_inquiry.`id`
                WHERE
                {$where} Order by id DESC limit 1";



        $inquiry_info = $this->dbh->select_row($sql);
        
        //获取询价单相关信息
        $where = " gl_inquiry.`is_del` = 0 AND gl_inquiry.`status` in (1,2) AND gl_inquiry.`id` = {$id}";
        $sql = "SELECT gl_inquiry.`cid` AS company_id,gl_inquiry.`status`,gl_inquiry.`gid`,gl_inquiry.`car_id`,gl_goods.`reach_endtime`,gl_goods.`cid` AS cargo_id,gl_goods.`weights`
                FROM gl_inquiry
                LEFT JOIN gl_goods ON gl_goods.`id` = gl_inquiry.`gid`
                WHERE
                {$where}";

        $inquiry = $this->dbh->select_row($sql);

        if(!$inquiry){
            return false;
        }


        //开始事物
        $this->dbh->begin();
        try{

            //修改货源状态
            $goods['status']  = 4;
            $result = $this->dbh->update('gl_goods',$goods,'id ='.$inquiry['gid']);
            if(!$result){
                $this->dbh->rollback();
                return false;
            }

            //新增托运单信息
            $params = array(
                'number' => COMMON::getCodeId('').mt_rand(100,999),
                'goods_id'=>$inquiry['gid'],//货源id
                'cargo_id'=>$inquiry['cargo_id'],//托运方
                'company_id'=>$inquiry['company_id'], //承运方
                'estimate_freight'=>round($inquiry_info['minprice'] * $inquiry['weights'],2),//预成交运费
                'updated_at'=>'=NOW()',
                'created_at'=>'=NOW()'
            );

            if(!empty($inquiry['car_id'])){
                $params['car_id'] = $inquiry['car_id'];
            }
            $order = $this->dbh->insert('gl_order',$params);
            if(empty($order)){
                $this->dbh->rollback();
                return false;
            }
            //不为空代表是回程车信息
            if(!empty($inquiry['car_id'])){
                //修改回程车信息状态
                $info['status']  = 6;//已生成托运单
                $info['order_id']  = $order;//已生成托运单
                $result = $this->dbh->update('gl_return_car',$info,'id ='.$inquiry['car_id']);
                if(!$result){
                    $this->dbh->rollback();
                    return false;
                }
            }

            //修改询价单信息
            $updata_inquiry = array(
                'order_id' =>$order,
                'status' =>3,
                'price' =>$inquiry_info['minprice'],
                'updated_at' =>'=NOW()'
            );
            $data = $this->dbh->update('gl_inquiry',$updata_inquiry,'id ='.$id);
            if(empty($data)){
                $this->dbh->rollback();
                return false;
            }

            $this->dbh->commit();
            return $order;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }

    }
    /**
     * 货主找车议价
     */
    public function addPublishAndCreateInquiry($params){

        //1 添加货源信息 2 生成询价单  3 添加询价记录


        //开始事物
        $this->dbh->begin();
        try{

            $goods_info  = $params;
            unset($goods_info['car_id']);
            unset($goods_info['stype']);

            $gid = $this->dbh->insert('gl_goods',$goods_info);
            if(!$gid){
                $this->dbh->rollback();
                return false;
            }

            $insertInfo = array(
                'gid'=>$gid,
                'cid'=>$params['carriers_id'],//承运商id
                'status'=>1,
                'created_at'=>'=NOW()',
                'updated_at'=>'=NOW()',
            );

            if (isset($params['car_id']) && !empty($params['car_id'])) {
                $insertInfo['car_id'] = $params['car_id'];

            }

            $inquiry = $this->dbh->insert('gl_inquiry',$insertInfo);
            if(!$inquiry){
                $this->dbh->rollback();
                return false;
            }

            //修改回程车信息状态
            if (isset($params['car_id']) && !empty($params['car_id'])) {
                $info['status']  = 2;
                $info['inquiry_id']  = $inquiry;
                $result = $this->dbh->update('gl_return_car',$info,'id ='.$params['car_id']);
                if(!$result){
                    $this->dbh->rollback();
                    return false;
                }
            }


            //添加询价记录
            $info = array(
                    'pid'=>$inquiry,
                    'minprice'=>$params['offer_price'],
                    'pid'=>$inquiry,
                    'cid'=>$params['cid'],
                    'type'=>2,
                    'created_at'=>'=NOW()',
                    'updated_at'=>'=NOW()'
            );

            $re = $this->dbh->insert('gl_inquiry_info',$info);
            if(!$re){
                $this->dbh->rollback();
                return false;
            }


            $this->dbh->commit();
            return $inquiry;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }



    }


}
