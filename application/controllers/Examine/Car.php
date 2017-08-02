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
}
