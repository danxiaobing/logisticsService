<?php
/**
 * 供应商
 * Created by PhpStorm.
 * User: amor
 * Date: 2017/8/1
 * Time: 15:49
 */

class Examine_CarrierModel
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
    public function getCarrierList($params){

        $filter = array();
        $where = '';

        #检测参数
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filter[] = " gl_companies.`company_name`  LIKE '%{$params['company_name']}%' ";
        }
        if (isset($params['company_user']) && $params['company_user'] != '') {
            $filter[] = " gl_companies.`company_user`  LIKE '%{$params['company_user']}%' ";
        }
        if (isset($params['company_code']) && $params['company_code'] != '') {
            $filter[] = " gl_companies.`company_code`   LIKE '%{$params['company_code']}%' ";
        }

        $where .= ' gl_companies.`status` != 0 ';
        $where .= ' AND gl_companies_business.`is_del` = 0 ';
        #条件
        if (0 != count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }

//        var_dump($where);die;
        #设置分页参数
        $page = isset($params['pageCurrent'])? intval($params['pageCurrent']) : 1;
        $pageSize = isset($params['pageSize'])? intval($params['pageSize']) : 9;


        #sql语句
        $sql = "SELECT gl_companies.id,gl_companies.company_code,gl_companies.province_id,gl_companies.company_name,gl_companies.city_id,gl_companies.area_id,gl_companies.company_address,gl_companies.company_user,gl_companies.company_telephone,gl_companies.status,conf_area.area,conf_province.province,conf_city.city,gl_companies_business.type as business,gl_companies_products.type as products FROM gl_companies 
                LEFT JOIN gl_companies_business ON gl_companies_business.cid = gl_companies.id 
                LEFT JOIN gl_companies_products ON gl_companies_products.cid = gl_companies.id 
                LEFT JOIN conf_area ON conf_area.areaid = gl_companies.area_id
                LEFT JOIN conf_province ON conf_province.provinceid = gl_companies.province_id
                LEFT JOIN conf_city ON conf_city.cityid = gl_companies.city_id WHERE 
                {$where} 
                ORDER BY gl_companies.`updated_at` DESC";

        $countSql = "SELECT COUNT(1) FROM gl_companies WHERE is_del = 0 AND status != 0";

        $data['total'] = $this->dbh->select_one($countSql);
        $this->dbh->set_page_num($page);
        $this->dbh->set_page_rows($pageSize);

        $data['list'] =  $this->dbh->select($sql);

        return $data;
    }

    /**
     * 承运商
     * @param int $params
     * @return array $data
     */
    public function getCarrier($id){
        $where = ' gl_companies.id = '.intval($id);
        $where .= ' AND gl_companies.`is_del` = 0 ';
        $where .= ' AND gl_products.`is_del` = 0 ';

        #sql语句
        $sql = "SELECT gl_companies.id,gl_companies.company_code,gl_companies.province_id,gl_companies.company_name,gl_companies.city_id,gl_companies.area_id,gl_companies.company_address,gl_companies.company_user,gl_companies.company_telephone,gl_companies.status,gl_products.type FROM gl_companies 
                LEFT JOIN gl_products ON gl_products.cid = gl_companies.id WHERE 
               ".$where;

        $data =  $this->dbh->select($sql);

        return $data;
    }

    /**
     * 审核
     * @param array $params
     * @return array $data
     */
    public function updateCarrier($status,$where){
        return $this->dbh->update('gl_companies', $status, $where );
    }


    public function showfile($id){
        $sql = "SELECT  danger_file,business_license,other_file FROM gl_companies WHERE id = ".intval($id);
        $res = $this->dbh->select_row($sql);
        return $res;
    }

}