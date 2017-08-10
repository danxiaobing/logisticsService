<?php
/**
 * @author  Jeff
 * @date    2017-8-1
 * @version $Id$
 */

class Examine_CarController extends Rpc {

    public function init() {
        parent::init();
    }

    public function getPageFunc($params)
    {
        $L = new Examine_CarModel(Yaf_Registry::get("db"));
        $data = $L->getPage($params);
        return $data;
    }

    public function showfileFunc($id)
    {
        $L = new Examine_CarModel(Yaf_Registry::get("db"));
        $data = $L->showfile($id);
        return $data;
    }

    public function updateFunc($params,$where)
    {
        $L = new Examine_CarModel(Yaf_Registry::get("db"));
        $data = $L->update($params,$where);
        return $data;
    }

    /**
     * 新增
     */
    public function addFunc($data)
    {
        //print_r($data);die;
        $S = new Examine_CarModel(Yaf_Registry:: get("db"));
        $list = $S->add($data);
        return $list;
    }

    /**
     * 细节
     */
    public function getInfoFunc($id = 0)
    {
        $S = new Examine_CarModel(Yaf_Registry:: get("db"));
        $data = $S->getInfo($id);
        return $data;
    }

    /**
     * 删除
     */
    public function delFunc($id = 0, $data = array())
    {
        $L = new Examine_CarModel(Yaf_Registry::get("db"));
        $data = $L->del($id, $data);
        return $data;
    }
}
