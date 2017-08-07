<?php
/**
 * Created by PhpStorm.
 * User: amor
 * Date: 2017/8/5
 * Time: 13:48
 */
class Examine_CooperationController extends Rpc
{


    public function init()
    {
        parent::init();

    }

    /**
     * 获取承运商列表
     * @param array $params
     * @return array $data
     */
    public function getCooperationListFunc($params){
        $Cooperation = new Examine_CooperationModel(Yaf_Registry::get("db"));
        $data = $Cooperation->getCooperationList($params);
        return $data;
    }

    /**
     * 修改
     * @param array  $status
     * @param integer $where
     * @return array
     */
    public function updateCooperationFunc($status,$where){
        $Cooperation = new Examine_CooperationModel(Yaf_Registry::get("db"));
        $data = $Cooperation->updateCooperation($status,$where);
        return $data;
    }

    public  function showfileFunc($id){
        $Cooperation = new Examine_CooperationModel(Yaf_Registry::get("db"));
        $data = $Cooperation->showfile($id);
        return $data;
    }
}
