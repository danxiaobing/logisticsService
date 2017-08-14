<?php
/**
 * @author  Andy
 * @date    2017-8-1
 * @version $Id$
 */

class Examine_DriverController extends Rpc {

    public function init() {
        parent::init();
        
    }

    //获取司机信息
    public function getDriverInfoFunc($serach){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->getDriverInfo($serach);
    }

    //更新审核状态
    public function updateStatusFunc($status,$where){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->updateStatus($status,$where);	
    }

    //证件查看
    public function getPicFunc($id){
    	$D = new Examine_DriverModel(Yaf_Registry::get("db"));
    	return $D->getPic($id);
    }

    //隶属公司
    public function getCompanyFunc($companyid,$include_self = true){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->getCompany($companyid,$include_self);
    }


    //前台页面根据id获取数据
    public function getInfoByIdFunc($id){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->getInfoById($id);
    }

    //删除信息
    public function delByIdFunc($id){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->delById($id);       
    }


    //启用功能
    public function enabledFunc($id,$param){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->enabled($id,$param);
    }


    //前台司机新增
    public function insertDataFunc($input){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->insertData($input);   
    }

    //司机编辑图片展示
    public function getDiverPicFunc($id){
        $D = new Examine_DriverModel(Yaf_Registry::get("db"));
        return $D->getDiverPic($id);          
    }


}
