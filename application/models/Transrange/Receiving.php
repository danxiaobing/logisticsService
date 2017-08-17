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
        $res = $this->dbh->insert('gl_rule', $params);
        if( $res ){
            foreach ($products as $key => $value) {
                $value['rule_id'] = $res;
                $this->dbh->insert('gl_rule_product', $value );
            }
            return $res;
        }
        return false;
    }

    public function update($params, $id)
    {
        $products = $params['products'];
        unset($params['products']);
        $res = $this->dbh->update('gl_rule', $params, 'id ='.$id);
        if( $res ){
            $this->dbh->update('gl_rule_product', array('is_del'=>1), 'rule_id ='.$id);
            foreach ($products as $key => $value) {
                $value['rule_id'] = $res;
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
    
}
