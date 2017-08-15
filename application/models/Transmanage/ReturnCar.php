<?php

/**
 * 回程车
 * User: Daley
 */
class Transmanage_ReturnCarModel
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
        $order = "updated_at";

        if (isset($params['order']) && $params['order'] != '') {
            $order = $params['order'];
        }
        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " `status`=" . $params['status'];
        }
        if (isset($params['company_id']) && $params['company_id'] != '') {

            $filter[] = " `company_id`=" . $params['company_id'];
        }
        if (count($filter) > 0) {
            $where .= implode(" AND ", $filter);
        }

        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1)
                FROM `gl_return_car`
                {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT `id`,
                       `start_pid`,
                       `start_cid`,
                       `start_aid`,
                       `goal_pid`,
                       `goal_cid`,
                       `goal_aid`,
                       `start_time`,
                       `end_time`,
                       `freight_type`,
                       `start_weight`,
                       `end_weight`,
                       `cid`,
                       `freight`,
                       `status`
                     FROM `gl_return_car`
                     {$where}
                     ORDER BY status=3 ASC, `{$order}` DESC";
        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }
   //获取详细
    public function getInfo($fields=null,$where=null){
        $sql = "SELECT $fields FROM `gl_return_car` WHERE `is_del`= 0 ";
        if($where)$sql .= "AND $where";
        return $this->dbh->select($sql);
    }
    //添加信息
    public function addInfo($params)
    {
        return $this->dbh->insert('gl_return_car',$params);
    }
    //修改信息
    public function update($params, $id)
    {
        return $this->dbh->update('gl_return_car',$params,'id=' . intval($id));
    }
    //删除
    public function del($id)
    {
        return $this->dbh->delete('gl_return_car','id=' . intval($id));
    }


}