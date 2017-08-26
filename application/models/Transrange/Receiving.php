<?php
/**
 * Created by PhpStorm.
 * User: Jeff
 * Date: 2016/8/14
 * Time: 18:32
 */
class Transrange_ReceivingModel
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


    public function getPage($params)
    {
        $filed = array();
        $filter[] = " WHERE r.`is_del` = 0";
        $where = "  ";


        if (isset($params['product']) && $params['product'] != '' ) {
            $filter[] = " p.`zh_name` LIKE '%{$params['product']}%' ";
        }


        if (isset($params['min_load']) && $params['min_load'] != '' && $params['min_load'] != '0') {
            $filter[] = " r.`max_load` >=  '{$params['min_load']}' ";
        }

        if (isset($params['max_load']) && $params['max_load'] != '' && $params['max_load'] != '0') {
            $filter[] = " r.`min_load` <=  '{$params['max_load']}' ";
        }



        if (isset($params['set_line']) && $params['set_line'] != '' ) {
            $filter[] = " r.`set_line` = '{$params['set_line']}' ";
        }
        if (isset($params['set_rule']) && $params['set_rule'] != '' ) {
            $filter[] = " r.`set_rule` = '{$params['set_rule']}' ";
        }
        if (isset($params['price_type']) && $params['price_type'] != '' ) {
            $filter[] = " r.`price_type` =  '{$params['price_type']}' ";
        }

        if( isset($params['start_provice_id']) && $params['start_provice_id'] != '' && $params['start_provice_id'] != '0'){
            if( isset($params['start_city_id']) && $params['start_city_id'] != '' && $params['start_city_id'] != '0'){
                $filter[] = " ( r.`start_provice_id` = 0 OR r.`start_city_id` = 0 OR r.`start_city_id` = '{$params['start_city_id']}' ) ";
            }else{
                //全省
                $filter[] = " ( r.`start_provice_id` = 0 OR r.`start_provice_id` = '{$params['start_provice_id']}' ) ";
            }
        }

        if( isset($params['end_provice_id']) && $params['end_provice_id'] != '' && $params['end_provice_id'] != '0'){
            if( isset($params['end_city_id']) && $params['end_city_id'] != '' && $params['end_city_id'] != '0'){
                $filter[] = " ( r.`end_provice_id` = 0 OR r.`end_city_id` = 0 OR r.`end_city_id` = '{$params['end_city_id']}' ) ";
            }else{
                //全省
                $filter[] = " ( r.`end_provice_id` = 0 OR r.`end_provice_id` = '{$params['end_provice_id']}' ) ";
            }
        }



        if (1 <= count($filter)) {
            $where .= implode(' AND ', $filter);
        }else{
            $where = "";
        }

        $sql = "
        select count(*) from (
        SELECT 
        COUNT(1)
        FROM `gl_rule` as r
        LEFT JOIN  gl_rule_product as rg ON rg.`rule_id` = r.`id`
        LEFT JOIN  gl_products as p ON p.`id` = rg.`product_id`
        {$where} 
        GROUP BY r.`id`
        ) as count";

        // print_r($sql);die;   
        $result = $params;
        $result['totalRow'] = $this->dbh->select_one($sql);

        $result['list'] = array();
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);


        $sql = "SELECT 
        r.*,substring_index(GROUP_CONCAT(p.zh_name separator ','),',',3) as products
        FROM `gl_rule` as r
        LEFT JOIN  gl_rule_product as rg ON rg.`rule_id` = r.`id`
        LEFT JOIN  gl_products as p ON p.`id` = rg.`product_id`
        {$where} 
        GROUP BY r.`id`
        ORDER BY r.`updated_at` DESC";
        // echo "<pre>";print_r($sql);echo "</pre>";die; 
        $result['list'] = $this->dbh->select_page($sql); 
        if( count($result['list']) ){
            foreach ($result['list'] as $k => $v) {

                // if( $v['start_province_id'] == '0' ){
                //     break;
                // }
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


    public function getInfo($id)
    {
        $sql = "SELECT * FROM `gl_rule`  WHERE `id` = {$id} ";
        return $this->dbh->select_row($sql);
    }

    public function add($params)
    {        
        $products = $params['products'];
        unset($params['products']);

        $user_list = $params['user_list'];
        unset($params['user_list']);

        //事务
        $this->dbh->begin();
        try{
            //gl_companies_range 插入基本信息
            $id = $this->dbh->insert('gl_rule', $params);
            if(!$id){
                //回滚
               $this->dbh->rollback();
               return false;
            }

            //产品
            foreach ($products as $key => $value) {
                $value['rule_id'] = $id;
                $res2 = $this->dbh->insert('gl_rule_product', $value );
                if(!$res2){
                    $this->dbh->rollback();
                    return false;                
                }
            }


            $user['user_list'] = $user_list;
            $user['rule_id'] = $id;
            $user['updated_at'] = '=NOW()';
            $user['created_at'] = '=NOW()';

            $res3 = $this->dbh->insert('gl_rule_firewall',$user);
            if(!$res3){
                $this->dbh->rollback();
                return false;                
            }


            $this->dbh->commit();
            return true;

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }

    public function updatePost($params, $id)
    {
        $products = $params['products'];
        unset($params['products']);

        $user_list = $params['user_list'];
        unset($params['user_list']);

        //事务
        $this->dbh->begin();
        try{
            //gl_companies_range 插入基本信息
            $res = $this->dbh->update('gl_rule', $params, 'id ='.$id);
            if(!$res){
                //回滚
               $this->dbh->rollback();
               return false;
            }

            //产品
            $re = $this->dbh->update('gl_rule_product', array('is_del'=>1), 'rule_id ='.$id);
            foreach ($products as $key => $value) {
                $value['rule_id'] = $id;
                $res2 = $this->dbh->insert('gl_rule_product', $value );
                if(!$res2){
                    $this->dbh->rollback();
                    return false;                
                }
            }


            $user = array(
                'user_list'=>$user_list,
                'updated_at' => '=NOW()',
            );

            $res3 = $this->dbh->update('gl_rule_firewall',$user,' rule_id ='.intval($id));
            if(!$res3){
                $this->dbh->rollback();
                return false;                
            }


            $this->dbh->commit();
            return true;

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }

    public function update($params,$id)
    {
        return $this->dbh->update('gl_rule',$params,'id = ' . intval($id));
    }

    public function del($id)
    {
        return $this->dbh->update('gl_rule',array('is_del'=>1),'id = ' . intval($id));
    }

    //获取所有
    public function getRualProducus($id){
        $sql = " SELECT category_id,product_id FROM gl_rule_product WHERE `is_del` = 0 AND `rule_id` = ".$id;
        return $this->dbh->select($sql);
    }
    
    //获取黑白名单
    public function getFileWall($rule_id){
        $sql = "SELECT user_list FROM gl_rule_firewall WHERE `rule_id` = ".intval($rule_id);
        return $this->dbh->select_one($sql);
    }



    //智能接单
    public function matching($params){

        //筛选产品
        $psql = "select p.rule_id from gl_rule_product as p where p.`is_del` = 0 AND p.`product_id` = ".$params['product_id'];
        $products = $this->dbh->select($psql);
        if( ! count($products) ){
            return false;
        }
        foreach ($products as $k => $v) {
            $pro_id .= $v['rule_id'] . ',';
        }
        $pro_id = substr($pro_id,0,-1);



        //基础筛选
        $filter = array();
        $filter[] = " WHERE r.`is_del` = 0 ";
        $filter[] = " r.`set_rule` = 1 ";
        $filter[] = " r.`is_use` = 1 ";
        $filter[] = " r.`id` in ({$pro_id}) ";
        
        if( isset($params['start_province_id']) && $params['start_province_id'] != '' && $params['start_province_id'] != '0'){
            if( isset($params['start_city_id']) && $params['start_city_id'] != '' && $params['start_city_id'] != '0'){
                if( isset($params['start_area_id']) && $params['start_area_id'] != '' && $params['start_area_id'] != '0'){
                    //全县
                    $filter[] = " ( r.`start_province_id` = 0 OR r.`start_city_id` = 0 OR r.`start_area_id` = 0 OR r.`start_area_id` = '{$params['start_area_id']}' ) ";
                }else{
                    //全市
                    $filter[] = " ( r.`start_province_id` = 0 OR OR r.`start_city_id` = 0 r.`start_city_id` = '{$params['start_city_id']}' ) ";
                }
            }else{
                //全省
                $filter[] = " ( r.`start_province_id` = 0 OR r.`start_province_id` = '{$params['start_province_id']}' ) ";
            }
        }

        if( isset($params['end_province_id']) && $params['end_province_id'] != '' && $params['end_province_id'] != '0'){
            if( isset($params['end_city_id']) && $params['end_city_id'] != '' && $params['end_city_id'] != '0'){
                if( isset($params['end_area_id']) && $params['end_area_id'] != '' && $params['end_area_id'] != '0'){
                    //全县
                    $filter[] = " ( r.`end_province_id` = 0 OR r.`end_city_id` = 0 OR r.`end_area_id` = 0 OR r.`end_area_id` = '{$params['end_area_id']}' ) ";
                }else{
                    //全市
                    $filter[] = " ( r.`end_province_id` = 0 OR OR r.`end_city_id` = 0 r.`end_city_id` = '{$params['end_city_id']}' ) ";
                }
            }else{
                //全省
                $filter[] = " ( r.`end_province_id` = 0 OR r.`end_province_id` = '{$params['end_province_id']}' ) ";
            }
        }
        if( $params['loss'] ){
            $filter[] = " r.`loss` <= {$params['loss']} ";
        }
        
        if( $params['load'] ){
            $filter[] = " ( r.`min_load` <= {$params['load']} AND r.`max_load` >= {$params['load']}) ";
        }

        if( $params['car_type'] ){
            $filter[] = " r.`car_type` <= {$params['car_type']} ";
        }

        //黑白名单
        $filter[] = "
            IF (
                r.`fire` = 1,
                ! (f.`user_list` LIKE '%{$params['company_id']}%'),
                '1=1'
            )
            AND
            IF (
                r.`fire` = 2,
                f.`user_list` LIKE '%{$params['company_id']}%',
                '1=1'
            )
        ";

        //生成WHERE
        $where = "";
        if (1 <= count($filter)) {
            $where .= implode(' AND ', $filter);
        }
        

        $sql = "
            SELECT
                r.`cid`
            FROM
                gl_rule AS r
            LEFT JOIN gl_rule_firewall AS f ON r.`id` = f.`rule_id`
            {$where}
            ORDER BY
                r.`updated_at` desc;
        ";
        // return $sql;
        return $this->dbh->select($sql);
    }
}
