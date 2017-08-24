<?php

/**
 * 货主--货源管理
 * Class ucenter
 * @author  Daley
 * @date    2017-8-4
 * @version $Id$
 */
class Cargo_GoodsController extends Rpc {

    public function init() {
        parent::init();
    }



    /**
     * 搜索
     * @param array $paramsa
     * @author amor
     */
    public function searchGoodsFunc($params){
        $L = new Cargo_GoodsModel(Yaf_Registry::get("db"));
        $data = $L->searchGoods($params);
        return $data;
    }


    /**
     * 列表
     * @return 数组
     */
    public function getListFunc($params)
    {

        $L = new Cargo_GoodsModel(Yaf_Registry::get("db"));
        $data = $L->getlist($params);
        return $data;
    }
    /**
     * 新增
     */
    public function addFunc($data)
    {
        $S = new Cargo_GoodsModel(Yaf_Registry:: get("db"));
        $list = $S->addInfo($data);
        return $list;
    }

    /**
     * 细节
     */
    public function getInfoFunc($id)
    {
        $S = new Cargo_GoodsModel(Yaf_Registry:: get("db"));
        $data = $S->getInfo($id);
        return $data;
    }

    /**
     * 更新
     */
    public function updateFunc($data = array(),$id)
    {
        $L = new Cargo_GoodsModel(Yaf_Registry::get("db"));
        $data = $L->updata($data,$id);
        return $data;
    }

    /**
     * 删除
     */
    public function delFunc($id = 0)
    {
        $L = new Cargo_GoodsModel(Yaf_Registry::get("db"));
        $data = $L->delete($id);
        return $data;
    }




}