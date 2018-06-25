<?php
/**
 * Created by PhpStorm.
 * User: daley
 * Date: 2018/4/28
 * Time: 10:01
 */
class Api_ApiModel{


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
     * 查询专线车-回程车
     */
    public function getBackAndLineCarPage($params){
        $filed = array();
        $filter_r[] = " WHERE r.`is_del` = 0 AND r.`status` = 1 ";//回程车
        $filter_z[] = " WHERE z.`is_del` = 0 AND z.`set_line` = 1 AND z.`is_use` = 1 AND p.`is_del`= 0 " ;//专线车
        $where_r = "  ";
        $where_z = "  ";

        $sortKeyArr = ['id','created_at','price'];
        $sortArr = ['DESC','ASC','desc','asc'];
        $sortStr = 'id';
        $sort = 'DESC';
        //排序关键词
        if (isset($params['sortkey']) && $params['sortkey'] != '') {
           if(in_array($params['sortkey'],$sortKeyArr)){
               $sortStr = $params['sortkey'];
           }
        }
        if (isset($params['sort']) && $params['sort'] != '') {
           if(in_array($params['sort'],$sortArr)){
               $sort = $params['sort'];
           }
        }



        //筛选承运商
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter_r[] = " r.`cid` = " . intval($params['cid']);
            $filter_z[] = " z.`cid` = " . intval($params['cid']);
        }
        if( isset($params['start_province_id']) && $params['start_province_id'] != '' && $params['start_province_id'] != '0'){
            if( isset($params['start_city_id']) && $params['start_city_id'] != '' && $params['start_city_id'] != '0'){
                if( isset($params['start_area_id']) && $params['start_area_id'] != '' && $params['start_area_id'] != '0'){
                    //全县
                    $filter_r[] = "  r.`start_province_id` = '{$params['start_province_id']}'  AND ( r.`start_city_id` = 0 OR r.`start_area_id` = 0 OR r.`start_area_id` = '{$params['start_area_id']}' ) ";
                    $filter_z[] = "  z.`start_province_id` = '{$params['start_province_id']}'  AND (z.`start_city_id` = 0 OR z.`start_area_id` = 0 OR z.`start_area_id` = '{$params['start_area_id']}' ) ";
                }else{
                    //全市
                    $filter_r[] = "  r.`start_province_id` = '{$params['start_province_id']}'  AND ( r.`start_city_id` = 0 OR r.`start_city_id` = '{$params['start_city_id']}' ) ";
                    $filter_z[] = "  z.`start_province_id` = '{$params['start_province_id']}'  AND ( z.`start_city_id` = 0 OR z.`start_city_id` = '{$params['start_city_id']}' ) ";
                }
            }else{
                //全省
                $filter_r[] = "  r.`start_province_id` = '{$params['start_province_id']}'  ";
                $filter_z[] = "  z.`start_province_id` = '{$params['start_province_id']}' ";
            }
        }

        if( isset($params['end_province_id']) && $params['end_province_id'] != '' && $params['end_province_id'] != '0'){
            if( isset($params['end_city_id']) && $params['end_city_id'] != '' && $params['end_city_id'] != '0'){
                if( isset($params['end_area_id']) && $params['end_area_id'] != '' && $params['end_area_id'] != '0'){
                    //全县
                    $filter_r[] = " r.`end_province_id` = '{$params['end_province_id']}' AND ( r.`end_city_id` = 0 OR r.`end_city_id` = '{$params['end_city_id']}' OR r.`end_area_id` = 0 OR r.`end_area_id` = '{$params['end_area_id']}' ) ";
                    $filter_z[] = " z.`end_province_id` = '{$params['end_province_id']}' AND ( z.`end_city_id` = 0 OR z.`end_city_id` = '{$params['end_city_id']}' OR z.`end_area_id` = 0 OR z.`end_area_id` = '{$params['end_area_id']}' ) ";
                }else{
                    //全市
                    $filter_r[] = "  r.`end_province_id` = '{$params['end_province_id']}' AND ( r.`end_city_id` = 0 OR r.`end_city_id` = '{$params['end_city_id']}' ) ";
                    $filter_z[] = "  z.`end_province_id` = '{$params['end_province_id']}' AND ( z.`end_city_id` = 0 OR z.`end_city_id` = '{$params['end_city_id']}' ) ";
                }
            }else{
                //全省
                $filter_r[] = "  r.`end_province_id` = '{$params['end_province_id']}'  ";
                $filter_z[] = "  z.`end_province_id` = '{$params['end_province_id']}'  ";
            }
        }

        //筛选分类
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $filter_r[] = " r.`category_id` = " . intval($params['category_id']);
            $filter_z[] = " p.`category_id` = " . intval($params['category_id']);
        }
        //筛选分类
        if (isset($params['category_id_two']) && !empty($params['category_id_two'])) {
            $filter_r[] = " r.`category_id_two` = " . intval($params['category_id_two']);
            $filter_z[] = " p.`produce_id` = " . intval($params['category_id_two']);
        }
        //筛选产品
        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $filter_r[] = " r.`product_id` = " . intval($params['product_id']);
            $filter_z[] = " p.`product_id` = " . intval($params['product_id']);
        }
        //筛选开始时间
        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter_r[] = " unix_timestamp(r.`start_time`) >= unix_timestamp('{$params['starttime']} 00:00:00')";
        }
        //筛选结束时间
        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter_r[] = " unix_timestamp(r.`end_time`) <= unix_timestamp('{$params['endtime']} 23:59:59')";
        }

        //重量
        if (isset($params['load']) && !empty($params['load'])) {
            $filter_z[] = " z.`max_load` >=  " . intval($params['load']);
            $filter_z[] = " z.`min_load` <=  " . intval($params['load']);

            $filter_r[] = " r.`max_load` >=  " . intval($params['load']);
            $filter_r[] = " r.`min_load` <=  " . intval($params['load']);
        }

        if (count($filter_r) > 0) {
            $where_r .= implode(" AND ", $filter_r);
        }
        if (count($filter_z) > 0) {
            $where_z .= implode(" AND ", $filter_z);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        /** 新增对回程车发车时间判断  **/
        $date = date('Y-m-d');
        $filter_where = "WHERE  com.`is_del` = 0 AND r.`is_del` = 0 AND r.`status` = 1  AND r.`end_time`<'{$date}'";
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter_where .= " AND r.`cid`=" . $params['cid'];
        }
        $sql = "SELECT r.id,r.end_time,r.status FROM gl_return_car AS r LEFT JOIN gl_companies AS com ON com.id = r.cid {$filter_where}";
        $list = $this->dbh->select($sql);

        if($list){
            foreach($list as $key=>$val){
                $info['status'] = 3;
                $this->dbh->update('gl_return_car',$info,'id ='.$val['id']);
            }
        }
        /** 对回程车发车时间判断 **/


        $sql = "SELECT COUNT(*) FROM(
                 SELECT z.start_province_id,z.start_city_id,z.id,z.cid,z.car_type,z.price_type,z.price,z.min_load,z.max_load,z.loss,p.product_id,1 AS ctype,com.company_name
                 FROM gl_rule AS z
                 LEFT JOIN gl_rule_product AS p ON p.rule_id = z.id
                 LEFT JOIN gl_companies AS com ON com.id = z.cid {$where_z}
                UNION
                 SELECT r.start_province_id,r.start_city_id,r.id,r.cid,0 AS car_type,r.price_type,r.price,r.min_load,r.max_load,r.loss,r.product_id,2 AS ctype,com.company_name
                 FROM gl_return_car AS r
                 LEFT JOIN gl_companies AS com ON com.id = r.cid {$where_r}
                ) AS ss ";
        $result['totalRow'] = $this->dbh->select_one($sql);
        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT '' as starttime,z.start_province_id,z.start_city_id,z.end_province_id,z.end_city_id,z.id,z.cid,z.car_type,z.price_type,z.price,z.min_load,z.max_load,z.loss,p.product_id,z.created_at,1 AS ctype,com.company_name
                 FROM gl_rule AS z
                 LEFT JOIN gl_rule_product AS p ON p.rule_id = z.id
                 LEFT JOIN gl_companies AS com ON com.id = z.cid {$where_z}
                UNION
                 SELECT r.start_time as starttime,r.start_province_id,r.start_city_id,r.end_province_id,r.end_city_id,r.id,r.cid,0 AS car_type,r.price_type,r.price,r.min_load,r.max_load,r.loss,r.product_id,r.created_at,2 AS ctype,com.company_name
                 FROM gl_return_car AS r
                 LEFT JOIN gl_companies AS com ON com.id = r.cid {$where_r}
                ORDER BY {$sortStr} {$sort} ";
        $result['list'] = $this->dbh->select_page($sql);
        if( count($result['list']) ){
            foreach ($result['list'] as $k => $v) {

                $type = 'area';
                if( $v['start_area_id'] == 0 ){
                    if( $v['start_city_id'] == 0 ){
                        $type = 'province';
                    }else{
                        $type = 'city';
                    }
                }
                $name = "start_{$type}_id";
                $sql = "SELECT GROUP_CONCAT(cp.`{$type}`) FROM conf_{$type} cp where cp.`{$type}id` = {$v[$name]}";
                $data = $this->dbh->select_one($sql);
                $result['list'][$k]['start_name'] = $data ? $data:'';

                $type = 'area';
                if( $v['end_area_id'] == 0 ){
                    if( $v['end_city_id'] == 0 ){
                        $type = 'province';
                    }else{
                        $type = 'city';
                    }
                }
                $name = "end_{$type}_id";
                $sql = "SELECT GROUP_CONCAT(cp.`{$type}`) FROM conf_{$type} cp where cp.`{$type}id` = {$v[$name]}";
                $data = $this->dbh->select_one($sql);
                $result['list'][$k]['end_name'] = $data ? $data:'';
            }
        }
        return $result;
    }

    /**
     * 根据运单号检测运单是否存在
     */
    public function checkConsignsByNumber($params){

        $filter[] = "";
        $where= "  WHERE `is_del` = 0 ";

        if (isset($params['number']) && !empty($params['number'])) {
            $filter[] = " `number` = " . trim($params['number']);
        }else{
            return false;
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }
        $sql = "SELECT id FROM gl_order {$where}";
        $res = $this->dbh->select_row($sql);
        return $res?$res['id']:false;

    }

    /**
     * 匹配运费价格
     * @params Date: 2018/5/17
     */
    public function matchingFreightPrice($params){

        $filter[] = "ru.`is_del` = 0 AND p.`is_del` = 0 AND ru.`price_type` = 2 AND ru.`is_use` = 1";
        $where = "  ";

        if(isset($params['start_province_id']) && !empty($params['start_province_id'])){
            $filter[] =  "ru.`start_province_id` = {$params['start_province_id']}";
        }else{
            return [];
        }
        if(isset($params['start_city_id']) && !empty($params['start_city_id'])){
            $filter[] =  " ru.`start_city_id` = {$params['start_city_id']}";
        }else{
            return [];
        }
        if(isset($params['start_area_id']) && !empty($params['start_area_id'])){
            $filter[] =  " ru.`start_area_id` = {$params['start_area_id']}";
        }else{
            return [];
        }
        if(isset($params['end_province_id']) && !empty($params['end_province_id'])){
            $filter[] =  " ru.`end_province_id` = {$params['end_province_id']}";
        }else{
            return [];
        }
        if(isset($params['end_city_id']) && !empty($params['end_city_id'])){
            $filter[] =  " ru.`end_city_id` = {$params['end_city_id']}";
        }else{
            return [];
        }
        if(isset($params['end_area_id']) && !empty($params['end_area_id'])){
            $filter[] =  " ru.`end_area_id` = {$params['end_area_id']}";
        }else{
            return [];
        }
        if(isset($params['product_id']) && !empty($params['product_id'])){
            $filter[] = " p.`product_id` = {$params['product_id']}";
        }else{
            return [];
        }
        if (1 <= count($filter)) {
            $where .= implode(' AND ', $filter);
        }else{
            $where = "";
        }
        $sql = "SELECT ru.`cid` as carriers_id,ru.`price`,com.`company_name`,com.`company_user`,com.`company_telephone`
                    FROM `gl_rule` AS ru
                    LEFT JOIN gl_companies AS com ON com.id = ru.cid
                    LEFT JOIN gl_rule_product AS p ON p.rule_id = ru.id
                    WHERE {$where} ORDER BY ru.`price` ASC ";
        return  $this->dbh->select($sql);

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


        if (isset($params['orderno']) && !empty($params['orderno'])) {
            $filter[] = " g.`orderno` = '{$params['orderno']}'";
        }else{
            return [];
        }
        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " g.`cid` = '{$params['cid']}'";
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " i.`status` = '{$params['status']}'";
        }


        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT i.id,
                  g.orderno,
                  g.weights,
                  g.desc_str,
                  g.price,
                  com.company_name,
                  i.order_id,
                  i.status,
                  i.created_at
                  FROM gl_inquiry i
                 LEFT JOIN gl_goods g ON   g.id=i.gid
                 LEFT JOIN gl_companies  com ON com.`id` = i.`cid`
                 WHERE  {$where}
                ORDER BY id DESC";

        return $this->dbh->select($sql);
    }







}