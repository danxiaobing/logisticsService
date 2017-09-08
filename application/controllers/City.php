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

    /**
     * 获取地区
     */
    public function getpalceFunc($palce)
    {
      $C = new CityModel(Yaf_Registry::get("db"));
        $data = $C->getpalce($palce);
        return $data;
    }
    //获取省市县json输出
    public function getPlaceListFunc($id){
      $C = new CityModel(Yaf_Registry::get("db"));
      return $C->getPlaceList($id);
    }

    //
}
