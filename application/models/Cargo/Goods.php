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
        if (isset($params['status']) && $params['status'] != '') {
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
               g.companies_name,
               g.off_starttime,
               g.off_endtime,
               g.reach_starttime,
               g.reach_endtime,
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
               g.reach_endtime,g.cars_type,g.loss,g.offer_status,g.offer_price,g.off_address,g.off_user,g.off_phone,g.reach_address,g.reach_user,g.reach_phone,g.consign_user,g.consign_phone,g.desc_str,g.status,
               gl_cars_type.name AS cars_type_name
               FROM gl_goods g
               LEFT JOIN gl_cars_type ON gl_cars_type.id = g.cars_type WHERE g.id=".$id;

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
            $filter[] = " unix_timestamp(g.`off_endtime`) <= unix_timestamp('{$params['off_endtime']} 00:00:00')";
        }
        if (isset($params['reach_starttime']) && $params['reach_starttime'] != '') {
            $filter[] = " unix_timestamp(g.`reach_starttime`) >= unix_timestamp('{$params['reach_starttime']} 00:00:00')";
        }
        if (isset($params['reach_endtime']) && $params['reach_endtime'] != '') {
            $filter[] = " unix_timestamp(g.`reach_endtime`) <= unix_timestamp('{$params['reach_endtime']} 00:00:00')";
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
