<?php

/**
 *账户申请验证
 * Created by PhpStorm.
 * User: z
 * Time: 10:29
 */
class Contract_ApplyController extends Rpc
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

    public function getContractApplyListFunc($params)
    {
        $ca = new Contract_ApplyModel(Yaf_Registry::get("db"));
        $data = $ca->getContractApplyList($params);
        return $data;
    }

    
    public function getContractApplyInfoFunc($id)
    {
        $ca = new Contract_ApplyModel(Yaf_Registry::get("db"));
        $data = $ca->getContractApplyInfoById($id);
        return $data;
    }

    /**
     * 增加/编辑申请
     * description
     * @Date   2017-03-08
     */
    public function changeContractApplyFunc($data,$apply_id)
    {
        $ca = new Contract_ApplyModel(Yaf_Registry::get("db"));
        return $ca->changeContractApply($data,$apply_id);
        # code...
    }
}