<?php
/**
 * 托运单
 * @author  Jeff
 * @date    2017-8-24
 */

class App_DispatchController extends Rpc
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
        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }

    /**
     * 修改状态
     * @param array $params
     * @author amor
     */
    public function dispatchProcedureFunc($params){
        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchProcedure($params);
        return $data;
    }

    public function dispatchPicFunc($id,$status){
        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchPic($id,$status);
        return $data;
    }


    public function dispatchListFunc($id){
        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->dispatchList($id);
        return $data;
    }

    public function getListByOrderidFunc($id){
        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        $data = $L->getListByOrderid($id);
        return $data;
    }

    /**
     * 待发车调度单
     * @param array $params
     * @author amor
     */
    public function getInfoFunc($params){

        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        $result = $L->getInfo($params);
        if(!empty($result['info'])){
            $cityArr = array_column($result['city'],'city','cityid');//城市数据 cityid-city
            $provinceArr = array_column($result['province'],'province','provinceid');//省数据 provinceid-province

            $ps_index = $result['info']['start_provice_id'];//起始省id
            $pe_index = $result['info']['end_provice_id'];//目的省id
            $s_index = $result['info']['start_city_id'];//起始城市id
            $e_index = $result['info']['end_city_id'];//起始城市id
            $res['provincestart'] = $provinceArr[$ps_index];//起始省
            $res['provinceend'] = $provinceArr[$pe_index];//目的省
            $res['citystart'] = $cityArr[$s_index];//起始城市
            $res['cityend'] = $cityArr[$e_index];//目的城市
             return array_merge($result['info'], $res);
        }
        return array();

    }


    /**
     * 编辑和新增
     */
    public function editDispatchFunc($params){
        $L = new App_DispatchModel(Yaf_Registry::get("db"));
        return $L->editDispatch($params);
    }

    /*确认发车条件*/
    public function queryInfoFunc($dispatch_id){
         $L = new App_DispatchModel(Yaf_Registry::get("db"));
         return $L->queryInfo($dispatch_id);       
    }


    public function getNeedListFunc($id){
         $L = new App_DispatchModel(Yaf_Registry::get("db"));
         return $L->getNeedLis($id);       
    }



}