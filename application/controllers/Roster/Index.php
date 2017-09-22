<?php

/**
 * 黑白名单
 * Class Index
 *  @author  Daley
 * @date    2017-08-08
 * @version $Id$
 */
class Roster_IndexController extends Rpc {

    public function init() {
        parent::init();
    }

    /**
     * 名单列表
     * @return 数组
     * @author Tina
     */
    public function getListFunc($where)
    {

        $L = new Roster_IndexModel(Yaf_Registry::get("db"));
        $data = $L->getList('id,join_id,cid,type',$where);
        return $data;
    }
    /**
     * 新增名单
     */
    public function addFunc($data)
    {
        $S = new Roster_IndexModel(Yaf_Registry:: get("db"));
        $list = $S->addRoster($data);
        return $list;
    }








}