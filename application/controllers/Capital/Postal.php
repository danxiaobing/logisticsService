<?php

/**
 * 中信出金审核
 * Created by PhpStorm.
 * User: xingjun
 * Date: 2017/3/21
 * Time: 10:29
 */
class Capital_PostalController extends Rpc
{
    /**
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        #   Yaf_Dispatcher::getInstance()->disableView();
    }

    public function getPostalListFunc($params)
    {
        $Cp = new Capital_PostalModel(Yaf_Registry::get("db"));
        $data = $Cp->getPostalList($params);
        return $data;
    }

    public function getPostalInfoFunc($id)
    {
        $Cp = new Capital_PostalModel(Yaf_Registry::get("db"));
        $data = $Cp->getPostalInfo($id);
        return $data;
    }

    public function saveAuditStatusFunc($params, $pri_id)
    {
        $Cp = new Capital_PostalModel(Yaf_Registry::get("db"));
        $data = $Cp->save($params, $pri_id);
        return $data;
    }
}
