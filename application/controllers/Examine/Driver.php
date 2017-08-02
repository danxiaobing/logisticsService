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


}
