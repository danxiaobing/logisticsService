<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17
 * Time: 10:28
 */
class App_UsercenterController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 意见反馈
     * @param array $params
     * @return array $data
     */
    public function feedbackFunc($params){
        $feedback = new App_UsercenterModel(Yaf_Registry::get("db"));
        $data = $feedback->add($params);
        return $data;
    }

    public function getVersionsFunc($type){
        $Auth = new App_UsercenterModel(Yaf_Registry:: get("gy_db"));
        return $Auth->getVersions($type);
    }

}