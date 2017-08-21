<?php

/**
 * 获取公司信息
 *
 * @author  Daley
 * @date    2016-08-08
 * @version $Id$
 */
class CompaniesModel
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
    public function getCompanysIDFunc($id = 0)
    {
        $L = new Examine_CarModel(Yaf_Registry::get("db"));
        $data = $L->getCompanysID($id);
        return $data;
    }


}
