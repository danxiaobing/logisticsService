<?php

/**
 * Bootstrap, 应用操作处理之前预定义/处理
 * 系统会依次处理 _init 开头的方法
 *
 * @author  James
 * @date    2011-12-31 17:18
 * @version $Id$
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

    /**
     * 初始化全局变量及对象
     */
    public function _initVariables(Yaf_Dispatcher $dispatcher)
    {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config",$config);

        $smsrpc = $config->get("smsrpc");
        Yaf_Registry::set("smsrpc", $smsrpc);


        $db = $config->get("db");
        Yaf_Registry::set("db", new MySQL(
            $db->host,
            $db->port,
            $db->username,
            $db->password,
            $db->default,
            $db->charset
        ));


        //连接gouyie数据库
        $gy_db = $config->get("gy_db");
        Yaf_Registry::set("gy_db", new MySQL(
            $gy_db->host,
            $gy_db->port,
            $gy_db->username,
            $gy_db->password,
            $gy_db->default,
            $gy_db->charset
        ));
    }

    /**
     * 注册插件
     */
    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {

        $config = Yaf_Application::app()->getConfig();
        $sys = $config->get("sys");

        if ($sys->encrypt) {
            $g = new GlobalPlugin();
            $dispatcher->registerPlugin($g);
        }
    }

    /**
     * 指定视图引擎
     */
    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->setView(new View(APPLICATION_PATH . '/application/views',APPLICATION_PATH . '/application/cache'));
        #Yaf_Dispatcher::getInstance()->disableView();
    }

}