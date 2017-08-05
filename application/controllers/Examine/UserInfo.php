<?php
/**
 * Created by PhpStorm.
 * User: amor
 * Date: 2017/8/3
 * Time: 13:29
 */

class Examine_UserInfoController extends Rpc
{


    public function init()
    {
        parent::init();

    }

    /**
     * 获取承运商列表
     * @param array $params
     * @return array $data
     */
    public function getUserInfoListFunc($params){
        $User = new Examine_UsersModel(Yaf_Registry::get("db"));
        $data = $User->getUserList($params);
        return $data;
    }

    /**
     * 修改承运商信息
     * @param integer $id
     * @param array $params
     * @return bool
     */
    public function updateUserInfoFunc($id,$params){
        $User = new Examine_UsersModel(Yaf_Registry::get("db"));
        $data = $User->updateUser($id,$params);
        return $data;
    }

    /**
     * 获取承运商信息
     * @param string $params
     * @param string $password
     * @return array
     */
    public function getUserInfoFunc($params,$password = ''){

        $User = new Examine_UsersModel(Yaf_Registry::get("db"));
        $data = $User->getUser($params,$password);
        return $data;
    }

    /**
     * 注册承运商
     * @param $params
     * @return array
     */
    public function registerPostFunc($params){
        $User = new Examine_UsersModel(Yaf_Registry::get("db"));
        $data = $User->register($params);
        return $data;
    }


}