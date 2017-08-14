<?php
/**
 * @author  Andy
 * @date    2017-8-8
 * @version $Id$
 */

class CityController extends Rpc {

    public function init() {
        parent::init();
        
    }

    //获取省市县json输出
    public function getPlaceListFunc(){
      $C = new CityModel(Yaf_Registry::get("db"));
      return $C->getPlaceList();
    }

    /**
     *  查找省的参数
     */
    public function getprovinceFunc()
    {
    	$C = new CityModel(Yaf_Registry::get("db"));
        $data = $C->getprovince();
        return $data;
    }
    /**
     * 根据省的ID 查找市级别
     */
    public function getcityFunc($id,$table)
    {
    	$C = new CityModel(Yaf_Registry::get("db"));
        if($table == 'conf_city'){
            //查询省信息
            $info = $C->getInfo("provinceid = $id", 'conf_province');
            $sysno = $info['provinceid'];
        }
        if($table == 'conf_area'){
            //查询市信息
            $info = $C->getInfo("cityid = $id", 'conf_city');
            $sysno = $info['cityid'];
        }
        $data = $C->getcity($sysno,$table);

        return $data;
    }

    /**
     * 获取市的参数
     */
    public function getcityByIdFunc($id)
    {
    	$C = new CityModel(Yaf_Registry::get("db"));
        $data = $C->getcityById($id);
        return $data;
    }

    /**
     * 获取县的参数
     */
    public function getareaByIdFunc($id)
    {
    	$C = new CityModel(Yaf_Registry::get("db"));
        $data = $C->getareaById($id);
        return $data;
    }
}
