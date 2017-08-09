<?php

class Basicdata_CartypeController extends Rpc {

    public function init() {
        parent::init();
        
    }


    /**
     * 列表
     * @return 数组
     * @author Tina
     */
    public function getListFunc($params)
    {
        $L = new Basicdata_CartypeModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }


    /**
     * 新增
     */
    public function addFunc($data)
    {
        //print_r($data);die;
        $S = new Basicdata_CartypeModel(Yaf_Registry:: get("db"));
        $list = $S->add($data);
        return $list;
    }

    /**
     * 细节
     */
    public function getInfoFunc($id = 0)
    {
        $S = new Basicdata_CartypeModel(Yaf_Registry:: get("db"));
        $data = $S->getInfo($id);
        return $data;
    }
    
    /**
     * 更新
     */
    public function updateFunc($id = 0, $data = array())
    {
        $L = new Basicdata_CartypeModel(Yaf_Registry::get("db"));
        $data = $L->update($id, $data);
        return $data;
    }

    /**
     * 删除
     */
    public function delFunc($id = 0, $data = array())
    {
        $L = new Basicdata_CartypeModel(Yaf_Registry::get("db"));
        $data = $L->del($id, $data);
        return $data;
    }
}
