<?php
/**
 * @author  Daley
 * @date    2017-8-10
 * @version $Id$
 */

class Examine_FleetsController extends Rpc {

    public function init() {
        parent::init();
    }

    public function getPageFunc($params)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->getPage($params);
        return $data;
    }

    public function showfileFunc($id)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->showfile($id);
        return $data;
    }
    /**
     * 新增车队信息
     */
    public function addFunc($data)
    {
        $S = new Examine_FleetsModel(Yaf_Registry:: get("db"));
        $list = $S->addInfo($data);
        return $list;
    }
    /**
     * 获取承运商
     * @param $params
     * @param $where
     * @return mixed
     */
    public function getCompanyFunc(){

    }
    /**
     * @param $params
     * @param $where
     * @return mixed
     */
    public function updateFunc($params,$where)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->update($params,$where);
        return $data;
    }
}
