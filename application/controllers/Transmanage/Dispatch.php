<?php
/**
 * 托运单
 * @author  Jeff
 * @date    2017-8-24
 */

class Transmanage_DispatchController extends Rpc
{

    public function init()
    {
        parent::init();
    }


    /**
     * 搜索
     * @param array $params
     * @author amor
     */
    public function getListFunc($params){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }

    /**
     * 修改状态
     * @param array $params
     * @author amor
     */
    public function dispatchProcedureFunc($params){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchProcedure($params);
        return $data;
    }

    public function dispatchPicFunc($id){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchPic($id);
        return $data;
    }


    public function dispatchListFunc($id){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchList($id);
        return $data;
    }

    public function getListByOrderidFunc($id){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getListByOrderid($id);
        return $data;
    }

    /**
     * 待发车调度单
     * @param array $params
     * @author amor
     */
    public function getInfoFunc($dispatch_id){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        return $L->getInfo($dispatch_id);
    }


    /**
     * 编辑和新增
     */
    public function editDispatchFunc($params){
        $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
        return $L->editDispatch($params);
    }

    /*确认发车条件*/
    public function queryInfoFunc($dispatch_id){
         $L = new Transmanage_DispatchModel(Yaf_Registry::get("db"));
         return $L->queryInfo($dispatch_id);       
    }



}