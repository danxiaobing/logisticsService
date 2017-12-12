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
    public function getAllFleetsFunc($company_ids)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->getAllFleets($company_ids);
        return $data;
    }

    public function getFleetsFunc($cid)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->getFleets($cid);
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


    public function getInfoFunc($id){
        $S = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $S->getInfo($id);
        return $data;
    }
    /**
     * 更新车队信息
     * @param $params
     * @param $where
     * @return mixed
     */
    public function updateFunc($params,$id)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->update($params,$id);
        return $data;
    }
    /**
     * 删除车队信息
     */
    public function delFunc($id){
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->del($id);
        return $data;
    }
    /**
     * 获取运营商
     * @param $params
     * @param $where
     * @return mixed
     */
    public function getCompanyFunc($params,$where)
    {
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->getCompany($params,$where);
        return $data;
    }


    /**
     * 获取合作承运商
     * @param $id
     * @param $page
     * @return mixed
     */
    public function  getCooperateFunc($id,$page){
        $L = new Examine_FleetsModel(Yaf_Registry::get("db"));
        $data = $L->getCooperate($id,$page);
        return $data;
    }
}
