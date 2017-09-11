<?php
/**
 * 回程车
 * @author  Daley
 * @date    2017-8-14
 * @version $Id$
 */

class Transmanage_ReturncarController extends Rpc {

    public function init() {
        parent::init();
    }

    /**
     * 获取数据
     * @param $params
     * @return array
     */
   public function getPageFunc($params)
    {
        $L = new Transmanage_ReturnCarModel(Yaf_Registry::get("db"));
        $data = $L->getPage($params);
        return $data;
    }
    /**
     * 智能发布
     */
    public function fastBackCarFunc($params){
        $L = new Transmanage_ReturnCarModel(Yaf_Registry::get("db"));
        $data = $L->fastBackCar($params);
        return $data;
    }
    /**
     * 保存数据
     */
    public function fastFunc($params){
        $L = new Transmanage_ReturnCarModel(Yaf_Registry::get("db"));
        $data = $L->fast($params);
        return $data;
    }
    /**
     * 更新数据
     * @param $params
     * @param $where
     * @return mixed
     */
    public function updateFunc($params,$id)
    {
        $L = new Transmanage_ReturnCarModel(Yaf_Registry::get("db"));
        $data = $L->update($params,$id);
        return $data;
    }

    /**
     * 新增
     */
    public function addFunc($data)
    {
        //print_r($data);die;
        $S = new Transmanage_ReturnCarModel(Yaf_Registry:: get("db"));
        $list = $S->addInfo($data);
        return $list;
    }

    /**
     * 细节
     */
    public function getInfoFunc($id = 0)
    {
        $S = new Transmanage_ReturnCarModel(Yaf_Registry:: get("db"));
        $data = $S->getInfo($id);
        return $data;
    }

    /**
     * 删除
     */
    public function delFunc($id = 0, $data = array())
    {
        $L = new Transmanage_ReturnCarModel(Yaf_Registry::get("db"));
        $data = $L->del($id, $data);
        return $data;
    }
}
