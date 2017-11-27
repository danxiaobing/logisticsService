<?php

/**
 * 公司信息
 * Class Companies
 *  @author  Daley
 * @date    2017-08-08
 * @version $Id$
 */
class Roster_CompaniesController extends Rpc {

    public function init() {
        parent::init();
    }
    /**
     * 获取公司列表
     * @return array
     */
    public function getListFunc($where){
        $Cus = new Roster_CompaniesModel(Yaf_Registry::get("gy_db"));
        $res = $Cus->getList('id,company_name,en_companyname',$where);
        return $res;
    }
    /**
     * 获取单个公司信息
     */
     public function getOneFunc($where){
         $Cus = new Roster_CompaniesModel(Yaf_Registry::get("gy_db"));
         $res = $Cus->getList('id,company_name,en_companyname',$where);
         return $res;
     }


    /**
     * 获取合作承运商
     */
    public function getCompanysFunc($id = 0)
    {
        $L = new Roster_CompaniesModel(Yaf_Registry::get("db"));
        $data = $L->getCompanys($id);
        return $data;
    }

    /**
     * 获取承运商信息ById
     */
    public function getInfoByIdFunc($id){
        $L = new Roster_CompaniesModel(Yaf_Registry::get("db"));
        $data = $L->getInfoById($id);
        return $data;        
    }
}