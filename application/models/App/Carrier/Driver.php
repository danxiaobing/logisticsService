<?php
/**
 * Created by PhpStorm.
 * User: zhangbingxin
 * Date: 2018/6/22
 * Time: 15:27
 */
class App_Carrier_DriverModel
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

    public function getSearch($params){
        $filter = array();
        if (isset($params['keyword']) && $params['keyword']){
            $filter[] = " ( `name` like '%{$params['keyword']}%' OR `mobile` like '%{$params['keyword']}%')";
        }
        if (isset($params['company_id']) && $params['company_id']){
            $filter[] = "`company_id` = {$params['company_id']} ";
        }
        $where = ' status = 1 AND is_use = 1 AND isdelete = 0 ';
        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }
        $sql = "SELECT count(1) FROM gl_driver  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['page_size'] ? $params['page_size'] : 8);
        $sql = "SELECT * FROM gl_driver WHERE ".$where."ORDER BY created_at DESC";
        var_dump($sql);die();
        $result = $this->dbh->select_page($sql);
        if ($result){
            return $result;
        }else{
            return false;
        }
    }



}
