<?php
/**
 * @author  Andy
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
        //print_r(Yaf_Registry::get("db"));die;
        $data = $L->getPage($params);
        return $data;
    }
}
