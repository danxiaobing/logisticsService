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

        $where = 'gl_inquiry.is_del = 0 and gl_goods.is_del = 0 ';

        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " gl_goods.`start_provice_id` =".$params['start_provice_id'];
        }
        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " gl_goods.`cid` =".$params['cid'];
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


        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " gl_inquiry.`status` = '{$params['status']}'";
        }

        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " unix_timestamp(gl_inquiry.`created_at`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " unix_timestamp(gl_inquiry.`created_at`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_goods  LEFT JOIN gl_inquiry ON gl_inquiry.gid = gl_goods.id  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT 
                 gl_inquiry.id,
                 gl_goods.start_provice_id,
                 gl_goods.end_provice_id,
                 gl_goods.product_id,
                 gl_goods.weights,
                 gl_goods.price,
                 gl_inquiry.order_id,
                 gl_inquiry.status,
                 gl_inquiry.created_at,
                 gl_products.zh_name as product_name
                FROM gl_goods 
                LEFT JOIN gl_inquiry ON gl_inquiry.gid = gl_goods.id
                 LEFT JOIN gl_products ON gl_products.id = gl_goods.product_id
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
        $sql = "SELECT
               gl_inquiry.id,
               gl_inquiry.gid,
               gl_inquiry.price,
               gl_inquiry.type,
               gl_inquiry.status,
               gl_inquiry.cid,
               gl_inquiry.order_id,
               gl_goods.consign_user,
               gl_goods.consign_phone
               FROM gl_inquiry
               LEFT JOIN gl_goods ON gl_goods.id = gl_inquiry.gid
               WHERE gl_inquiry.is_del = 0 AND gl_inquiry.id=".$id." ORDER BY id DESC";
        $result['inquiry'] = $this->dbh->select_row($sql);

       //询价单记录信息
        $sql = "SELECT
                id,
                minprice,
                maxprice,
                cid,
                type,
                updated_at,
                created_at
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
    public function agreeOffer($id,$params){

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
        $where = " gl_inquiry.`is_del` = 0 AND gl_inquiry.`id` = {$id}";
        $sql = "SELECT gl_inquiry.`cid` AS company_id,gl_inquiry.`status`,gl_inquiry.`gid`,gl_goods.`reach_endtime`,gl_goods.`cid` AS cargo_id,gl_goods.`weights`
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
            //货源id
            $params['goods_id'] = $inquiry['gid'];
            //托运方
            $params['cargo_id'] = $inquiry['cargo_id'];
            //承运方
            $params['company_id'] = $inquiry['company_id'];
            //预成交运费
            $params['estimate_freight'] = $inquiry_info['minprice']*$inquiry['weights'];

            $res = $this->dbh->insert('gl_order',$params);
            if(empty($res)){
                $this->dbh->rollback();
                return false;
            }

            //修改询价单信息
            $updata_inquiry = array(
                'order_id' =>$res,
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
            return true;

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
            unset($goods_info['carriers_id']);
            unset($goods_info['offer_price']);
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

            $inquiry = $this->dbh->insert('gl_inquiry',$insertInfo);
            if(!$inquiry){
                $this->dbh->rollback();
                return false;
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
