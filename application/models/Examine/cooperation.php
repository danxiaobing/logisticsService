<?php
/**
 * Created by PhpStorm.
 * User: amor
 * Date: 2017/8/5
 * Time: 13:49
 */
class Examine_CooperationModel
{
    public $dbh = null;
    public $mc = null;

    /**
     * Constructor
     *
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mc = null)
    {
        $this->dbh = $dbh;
    }

    /**
     * 承运商列表
     * @param array $params
     * @return array $data
     */
    public function getCooperationList($params){

        $filter = array();
        $where = '';

        #检测参数
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filter[] = " gl_cooperation.`company_name`  LIKE '%{$params['company_name']}%' ";
        }
        if (isset($params['user']) && $params['user'] != '') {
            $filter[] = " gl_cooperation.`user`  LIKE '%{$params['company_name']}%' ";
        }
        if (isset($params['mobile']) && $params['company_code'] != '') {
            $filter[] = " gl_cooperation.`mobile`  LIKE '%{$params['mobile']}%' ";
        }

        $where .= ' gl_cooperation.`status` != 0 ';
        $where .= ' AND gl_cooperation.`is_del` = 0 ';
        #条件
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }


        #设置分页参数
        $page = isset($params['pageCurrent'])? intval($params['pageCurrent']) : 1;
        $pageSize = isset($params['pageSize'])? intval($params['pageSize']) : 9;


        #sql语句
        $sql = "SELECT gl_cooperation.id,gl_cooperation.status,gl_cooperation.name ,gl_cooperation.user,gl_cooperation.mobile,gl_cooperation.company_address,gl_cooperation.code,gl_cooperation.company_code,conf_area.area,conf_province.province,conf_city.city FROM gl_cooperation 
                LEFT JOIN conf_area ON conf_area.areaid = gl_cooperation.area_id
                LEFT JOIN conf_province ON conf_province.provinceid = gl_cooperation.province_id
                LEFT JOIN conf_city ON conf_city.cityid = gl_cooperation.city_id WHERE 
                {$where} 
                ORDER BY gl_cooperation.`updated_at` DESC";
        $countSql = "SELECT COUNT(1) FROM gl_cooperation WHERE is_del = 0 AND status != 0";

        $data['total'] = $this->dbh->select_one($countSql);
        $this->dbh->set_page_num($page);
        $this->dbh->set_page_rows($pageSize);

        $data['list'] =  $this->dbh->select($sql);

        return $data;
    }


    /**
     * 审核
     * @param array $params
     * @return array $data
     */
    public function updateCooperation($status,$where){
        return $this->dbh->update('gl_cooperation', $status, $where );
    }


    public function showfile($id){
        $sql = "SELECT  danger_file,business_license,other_file FROM gl_cooperation WHERE id = ".intval($id);
        $res = $this->dbh->select_row($sql);
        return $res;
    }

}
