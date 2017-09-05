<?php
/**
 * Created by PhpStorm.
 * 信息概览
 * User: Daley
 * Date: 2017/9/5
 * Time: 14:03
 */


class TodoController extends Rpc {

    public function init() {
        parent::init();

    }
    /**
     * 承运商--待办事项
     * @param array $paramsa
     * @author amor
     */
    public function carrierTodoFunc($params){
        $L = new TodoModel(Yaf_Registry::get("db"));
        $data = $L->carrierTodo($params);
        return $data;
    }
    /**
     * 货主--待办事项
     * @param array $paramsa
     * @author daley
     */
    public function cargoTodoFunc($params){
        $L = new TodoModel(Yaf_Registry::get("db"));
        $data = $L->cargoTodo($params);
        return $data;
    }


}