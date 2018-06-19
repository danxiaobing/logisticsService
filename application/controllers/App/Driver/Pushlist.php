<?php
/**
 * Created by PhpStorm.
 * Date: 2018/5/10
 * Time: 15:15
 */
class App_Driver_PushlistController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 获取消息推送列表
     * @param $params
     * @return mixed
     */
    public function getListFunc($params){
        $L = new App_Driver_PushlistModel(Yaf_Registry::get("db"));
        $data = $L->getList($params);
        return $data;
    }

    /**
     * 删除消息
     * @param $message_id
     * @return bool
     */
    public function delMessageFunc($message_id){
        $L = new App_Driver_PushlistModel(Yaf_Registry::get("db"));
        $data = $L->delMessage($message_id);
        return $data;
    }

    /**
     * 获取未读消息推送列表
     * @param $params
     * @return mixed
     */
    public function unreadlistFunc($params){
        $L = new App_Driver_PushlistModel(Yaf_Registry::get("db"));
        $data = $L->unreadlist($params);
        return $data;
    }

}