<?php

/**
 * 货主--待办事项
 * @author  Daley
 * @date    2017-8-31
 * @version $Id$
 */
class Cargo_TodoController extends Rpc {

    public function init() {
        parent::init();
    }



    /**
     * 搜索
     * @param array $paramsa
     * @author amor
     */
    public function statisticsFunc($params){
        $L = new Cargo_TodoModel(Yaf_Registry::get("db"));
        $data = $L->statistics($params);
        return $data;
    }




}