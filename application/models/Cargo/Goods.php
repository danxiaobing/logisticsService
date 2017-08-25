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
        $where = 'WHERE gl_goods.is_del = 0 ';
        $order = "gl_goods.updated_at";

        if (isset($params['order']) && $params['order'] != '') {
            if($params['order'] == 'o_s'){
                $order = 'gl_goods.off_starttime';
            }
            if($params['order'] == 'r_s'){
                $order = 'gl_goods.reach_starttime';
            }
        }
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " gl_goods.cid = " . $params['cid'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " `gl_goods.status`=" . $params['status'];
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
               gl_goods.status,
               gl_products.zh_name AS product_name
               FROM gl_goods
               LEFT JOIN gl_products ON gl_goods.product_id = gl_products.id
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
               gl_goods.id,
               gl_goods.start_provice_id,
               gl_goods.start_city_id,
               gl_goods.start_area_id,
               gl_goods.end_provice_id,
               gl_goods.end_city_id,
               gl_goods.end_area_id,
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
               gl_goods.offer_status,
               gl_goods.offer_price,
               gl_goods.off_address,
               gl_goods.off_user,
               gl_goods.off_phone,
               gl_goods.reach_address,
               gl_goods.reach_user,
               gl_goods.reach_phone,
               gl_goods.consign_user,
               gl_goods.consign_phone,
               gl_goods.desc_str,
               gl_goods.status,
               gl_products.zh_name AS product_name,
               gl_cars_type.name AS cars_type_name
               FROM gl_goods
               LEFT JOIN gl_products ON gl_goods.product_id = gl_products.id
               LEFT JOIN gl_cars_type ON gl_cars_type.id = gl_goods.cars_type WHERE gl_goods.id=".$id;

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
        $where = ' gl_goods.`is_del` = 0 AND gl_goods.`status` = 1 AND gl_goods.`reach_endtime`>  NOW()';

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
            $product = implode(',',$params['product_id']);
            $filter[] = "gl_goods.`product_id` in({$product})";
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }


        $sql = "SELECT count(1) FROM gl_goods  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT 
               gl_goods.id,
               gl_goods.start_provice_id,
               gl_goods.start_city_id,
               gl_goods.start_area_id,
               gl_goods.end_provice_id,
               gl_goods.end_city_id,
               gl_goods.end_area_id,
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
               gl_goods.status,
               gl_products.zh_name
                FROM gl_goods 
                LEFT JOIN gl_products ON gl_products.id = gl_goods.product_id
                WHERE  {$where}
                ORDER BY id DESC 
                ";


        $data = $this->dbh->select_page($sql);
        if(!empty($data)){
            $result['list'] = $this->city($data);
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
