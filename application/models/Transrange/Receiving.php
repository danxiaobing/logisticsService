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
        $filter[] = " WHERE `is_del` = 0";
        $where = "  ";

        if (isset($params['goods_name']) && $params['goods_name'] != '' && $params['goods_name'] != '0') {
            $filter[] = " `goods_name` LIKE '%{$params['goods_name']}%' ";
        }
        if (isset($params['date']) && $params['date'] != '' && $params['date'] != '0') {
            $filter[] = " `date` LIKE '%{$params['date']}%' ";
        }
        if (1 <= count($filter)) {
            $where .= implode(' AND ', $filter);
        }else{
            $where = "";
        }

        $sql = "SELECT COUNT(*) FROM `gl_rule` {$where}";
        //print_r($sql);die;   
        $result = $params;
        $result['totalRow'] = $this->dbh->select_one($sql);

        $result['list'] = array();


        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);


        $sql = "SELECT * FROM `gl_rule`{$where} ORDER BY `updated_at` DESC";
        $result['list'] = $this->dbh->select_page($sql);
        //echo "<pre>";print_r($result);echo "</pre>";die; 
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

        $user_list = $input['user_list'];
        unset($input['user_list']);
echo "<pre>";print_r($params);echo "</pre>";die; 
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



            $user_list['rule_id'] = $id;
            $user_list['updated_at'] = '=NOW()';
            $user_list['created_at'] = '=NOW()';

            $res3 = $this->dbh->insert('gl_rule_firewall',$user_list);
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

    public function update($params, $id)
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



        $products = $params['products'];
        unset($params['products']);
        $res = $this->dbh->update('gl_rule', $params, 'id ='.$id);
        if( $res ){
            $re = $this->dbh->update('gl_rule_product', array('is_del'=>1), 'rule_id ='.$id);
            foreach ($products as $key => $value) {
                $value['rule_id'] = $id;
                $this->dbh->insert('gl_rule_product', $value );
            }
            return $res;
        }
        return false;
    }

    public function del($id)
    {
        return $this->dbh->delete('gl_rule','id = ' . intval($id));
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
}
