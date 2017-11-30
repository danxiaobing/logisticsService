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
    
    public function getAllFleets($company_ids)
    {
        $ids = implode(',', $company_ids);
        $sql = "SELECT id,name FROM `gl_fleets`  WHERE `company_id` in ( {$ids} )";
        return $this->dbh->select($sql);
    }

    public function getFleets($cid)
    {
        $sql = "SELECT id,name FROM `gl_fleets`  WHERE `company_id` = {$cid} ";
        return $this->dbh->select($sql);
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
        #开启事物
        $this->dbh->begin();
        try{
            $res =  $this->dbh->delete('gl_fleets','id=' . intval($id));
            if(empty($res)){
                $this->dbh->rollback();
                return false;
            }else{

                $params['fleets_id'] = 0;
                $r = $this->dbh->update('gl_cars',$params,'fleets_id=' . intval($id));
                if(empty($r)){
                    $this->dbh->rollback();
                    return false;
                }
            }
            $this->dbh->commit();
            return true;

        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }

    public function getCooperate($id,$page){
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1)
                FROM `gl_fleets` AS fs
                LEFT JOIN `gl_companies` AS com ON com.`id` = fs.`company_id` 
                WHERE com.id = ".intval($id);


        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($page ? $page : 1);
        $this->dbh->set_page_rows(8);

        $sql = "SELECT
                fs.`id`,
                fs.`name` as fleet_name,
                fs.`fleet_user`,
                fs.`fleet_phone`,
                fs.`fleets_type`,
                fs.`is_use`,
                fs.`company_id`,
                com.`company_name`,
                COUNT(cars.`id`) as num
                FROM `gl_fleets` AS fs
                LEFT JOIN `gl_companies` AS com ON com.`id` = fs.`company_id`
                LEFT JOIN `gl_cars`  AS cars ON cars.`fleets_id` = fs.`id`
                WHERE com.id = {$id}
                GROUP BY fs.`id`
                ORDER BY fs.`updated_at` DESC";


        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }
}