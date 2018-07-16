<?php
/**
 * 托运单
 * @author  Jeff
 * @date    2017-8-24
 */

class App_Driver_DispatchController extends Rpc
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
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }

    /**
     * 修改状态
     * @param array $params
     * @author amor
     */
    public function dispatchProcedureFunc($params){
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchProcedure($params);
        return $data;
    }

    public function dispatchPicFunc($id,$status){
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchPic($id,$status);
        return $data;
    }


    public function dispatchListFunc($id){
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchList($id);
        return $data;
    }

    public function getListByOrderidFunc($id){
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getListByOrderid($id);
        return $data;
    }
    public function getDispatchInfoByIdFunc($id){
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getDispatchInfoById($id);
        return $data;
    }

    /**
     * 待发车调度单
     * @param array $params
     * @author amor
     */
    public function getInfoFunc($params){

        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        $result = $L->getInfo($params);
        return $result['info'];

    }


    /**
     * 编辑和新增
     */
    public function editDispatchFunc($params){
        $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
        return $L->editDispatch($params);
    }

    /*确认发车条件*/
    public function queryInfoFunc($dispatch_id){
         $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
         return $L->queryInfo($dispatch_id);       
    }


    public function getNeedListFunc($id){
         $L = new App_Driver_DispatchModel(Yaf_Registry::get("db"));
         return $L->getNeedLis($id);       
    }



}