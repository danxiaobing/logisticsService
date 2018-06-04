<?php

/**
 * 获取公司信息
 *
 * @author  Daley
 * @date    2016-08-08
 * @version $Id$
 */
class App_CompaniesModel
{
    public $dbh = null;

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
     * 获取公司列表
     * @param string $fields
     * @param string $where
     * @return array
     */
    public function getList($fields=null,$where=null)
    {
        $sql = "SELECT $fields FROM `td_companies` WHERE `delete`=0 ";
        if($where)$sql .= "AND $where";
        return $this->dbh->select($sql);
    }



    /**
     * 获取合作承运商
     */
    public function getCompanys($id){
        $sql = "SELECT id,company_name,company_user,company_telephone FROM `gl_companies` WHERE `is_del` = 0 AND `status` =2  AND  `pid` = {$id} ";
        return $this->dbh->select($sql);
    }

    /**
     * 获取承运商ById
     */
    public function getInfoById($id){
        $sql = "SELECT id,company_name,company_user,company_telephone FROM `gl_companies` WHERE `is_del` = 0  AND  `id` = {$id} ";
        return $this->dbh->select_row($sql);
    }

}
