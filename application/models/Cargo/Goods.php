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
        $where = 'WHERE is_del = 0 ';
        $order = "updated_at";

        if (isset($params['order']) && $params['order'] != '') {
            if($params['order'] == 'o_s'){
                $order = 'off_starttime';
            }
            if($params['order'] == 'r_s'){
                $order = 'reach_starttime';
            }
        }
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " cid = " . $params['cid'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " `status`=" . $params['status'];
        }
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1) FROM `gl_goods` {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
               id,
               start_provice_id,
               end_provice_id,
               cate_id,
               product_id,
               weights,
               price,
               companies_name,
               off_starttime,
               off_endtime,
               reach_starttime,
               reach_endtime,
               status
               FROM gl_goods " . $where . "   ORDER BY `{$order}` DESC";
        $result['list'] = $this->dbh->select_page($sql);
      //  print_r($sql);die;
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
               id,
               start_provice_id,
               end_provice_id,
               cate_id,
               product_id,
               weights,
               price,
               companies_name,
               off_starttime,
               off_endtime,
               reach_starttime,
               reach_endtime,
               cars_type,
               loss,
               off_address,
               off_user,
               off_phone,
               reach_address,
               reach_user,
               reach_phone,
               consign_user,
               consign_phone,
               desc_str,
               status
               FROM gl_goods WHERE id=".$id;

        return $this->dbh->select_row($sql);
    }
    //添加
    public function addInfo($params)
    {
        return $this->dbh->insert('gl_goods',$params);
    }

    //修改
    public function updata($params,$id)
    {
        return $this->dbh->update('gl_goods',$params,'id=' . intval($id));
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
        $where = 'is_del = 0 AND status = 1';

        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " gl_goods.`start_provice_id` =".$params['start_provice_id'];
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

        if (isset($params['off_endtime']) && $params['off_endtime'] != '') {
            $filter[] = " gl_goods.`off_endtime` <= '{$params['off_starttime']}'";
        }

        if (isset($params['off_starttime']) && $params['off_starttime'] != '') {
            $filter[] = " gl_goods.`off_starttime` >= '{$params['off_starttime']}'";
        }

        if (isset($params['reach_starttime']) && $params['reach_starttime'] != '') {
            $filter[] = " gl_goods.`reach_starttime` = '{$params['reach_starttime']}'";
        }

        if (isset($params['reach_endtime']) && $params['reach_endtime'] != '') {
            $filter[] = " gl_goods.`reach_endtime` = '{$params['reach_endtime']}'";
        }

        if (isset($params['product_id']) && $params['product_id'] != '') {
            $filter[] = "gl_goods.`product_id` in('{$params['product_id']}')";
        }

        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_goods  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT 
               gl_goods.id,
               gl_goods.start_provice_id,
               gl_goods.end_provice_id,
               gl_goods.cate_id,
               gl_goods.product_id,
               gl_goods.weights,
               gl_goods.price,
               gl_goods.companies_name,
               gl_goods.off_starttime,
               gl_goods.off_endtime,
               gl_goods.reach_starttime,
               gl_goods.reach_endtime,
               gl_goods.cars_type,
               gl_goods.loss,
               gl_goods.off_address,
               gl_goods.off_user,
               gl_goods.off_phone,
               gl_goods.reach_address,
               gl_goods.reach_user,
               gl_goods.reach_phone,
               gl_goods.consign_user,
               gl_goods.consign_phone,
               gl_goods.desc_str,
               gl_goods.status
                FROM gl_goods 
                WHERE  {$where}
                ORDER BY id DESC 
                ";

        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }

}
