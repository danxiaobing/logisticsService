<?php

/**
 * User: Daley
 */
class Examine_FleetsModel
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
        $filter[] = " WHERE 1=1";
        $where = "  ";

        if (isset($params['type']) && $params['type'] != '') {
            $filter[] = " fs.`fleets_type`=" . $params['type'];
        }
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filter[] = " com.`company_name` LIKE '%" . trim($params['company_name']) . "%'";
        }
        if (isset($params['fleets_user']) && $params['fleets_user'] != '') {
            $filter[] = " fs.`fleet_user` LIKE '%" . trim($params['fleets_user']) . "%'";
        }
        if (isset($params['company_id']) && $params['company_id'] != '') {

            $sql = "select id FROM `gl_companies` where pid=".$params['company_id'];
            $data = $this->dbh->select($sql);
            $newArr = array();
            if(!empty($data)){
                foreach($data as $key=>$val){
                    $newArr[]= $val['id'];
                }
            }
            $newArr[]= $params['company_id'];
            $filter[] = " fs.`company_id` in (" .  implode(",", array_values($newArr)) . ")";
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1)
                FROM `gl_fleets` AS fs
                LEFT JOIN `gl_companies` AS com ON com.`id` = fs.`company_id`
                {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
                fs.`id`,
                fs.`name` as fleet_name,
                fs.`fleet_user`,
                fs.`fleet_phone`,
                fs.`fleets_type`,
                fs.`is_use`,
                fs.`company_id`,

                com.`company_name`
                FROM `gl_fleets` AS fs
                LEFT JOIN `gl_companies` AS com ON com.`id` = fs.`company_id`
                {$where}
                ORDER BY fs.`updated_at` DESC";

        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }

    public function showfile($id)
    {
        $sql = "SELECT * FROM `gl_fleets` WHERE id = ".$id;
        $res = $this->dbh->select_row($sql);
        return $res;
    }
   /**
    * 获取承运商列表
    * @param string $fields
    * @param string $where
    * @return array
    */
    public function getCompany($fields=null,$where=null){
        $sql = "SELECT $fields FROM `gl_companies` WHERE `is_del`= 0 ";
            if($where)$sql .= "AND $where";
            return $this->dbh->select($sql);
    }

    public function getInfo($id){

        $sql = "SELECT fs.`id`,fs.`name`, fs.`fleet_user`, fs.`fleet_phone`, fs.`fleets_type`,  fs.`is_use`, fs.`company_id`, com.`company_name` FROM `gl_fleets` AS fs
                LEFT JOIN `gl_companies` AS com ON com.`id` = fs.`company_id` WHERE fs.id=".$id;
        return $this->dbh->select($sql);
    }
    //添加信息
    public function addInfo($params)
    {
        return $this->dbh->insert('gl_fleets',$params);
    }
    //修改信息
    public function update($params, $id)
    {
        return $this->dbh->update('gl_fleets',$params,'id=' . intval($id));
    }
    //删除
    public function del($id)
    {
        return $this->dbh->delete('gl_fleets','id=' . intval($id));
    }


}