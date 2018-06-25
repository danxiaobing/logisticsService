<?php
/**
 * Created by PhpStorm.
 * User: ai
 * Date: 2018/6/25
 * Time: 09:32
 */
class App_Carrier_ImController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    /**
     * 保存聊天信息
     * @param array $data
     * @return array $data
     */
    public function addChatFunc($data){
        $map = new App_Carrier_ImModel(Yaf_Registry::get("db"));
        $data = $map->addChatInfo($data);
        return $data;
    }
  



}