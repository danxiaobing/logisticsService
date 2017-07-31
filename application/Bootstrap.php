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
     * 把配置存到注册表
     */
//	function _initConfig(Yaf_Dispatcher $dispatcher)
//    {
//		$config = Yaf_Application::app()->getConfig();
//
//		Yaf_Registry::set("config",  $config);
//	}

    /**
     * 初始化全局变量及对象
     */
    public function _initVariables(Yaf_Dispatcher $dispatcher)
    {
        $config = Yaf_Application::app()->getConfig();

        #   Yaf_Registry::set("config",$config);

       $db = $config->get("db");
       Yaf_Registry::set("db", new MySQL(
           $db->host,
           $db->port,
           $db->username,
           $db->password,
           $db->default,
           $db->charset
       ));
//
//        $wpdb = $config->get("wp_db");
//        Yaf_Registry::set("wp_db", new MySQL(
//            $wpdb->host,
//            $wpdb->port,
//            $wpdb->username,
//            $wpdb->password,
//            $wpdb->default,
//            $wpdb->charset
//        ));
//
//        $wms_db = $config->get("wms_db");
//        Yaf_Registry::set("wms_db", new MySQL(
//            $wms_db->host,
//            $wms_db->port,
//            $wms_db->username,
//            $wms_db->password,
//            $wms_db->default,
//            $wms_db->charset
//        ));


        // $logistics_db = $config->get("logistics_db");
        // Yaf_Registry::set("logistics_db",  new MySQL(
        //     $logistics_db->host,
        //     $logistics_db->port,
        //     $logistics_db->username,
        //     $logistics_db->password,
        //     $logistics_db->default,
        //     $logistics_db->charset
        // ));

//        $logistics_db = $config->get("logistics_db");
//        Yaf_Registry::set("logistics_db", new MySQL(
//            $logistics_db->host,
//            $logistics_db->port,
//            $logistics_db->username,
//            $logistics_db->password,
//            $logistics_db->default,
//            $logistics_db->charset
//        ));
//
//        $storage_cloud = $config->get("storage_cloud_db");
//        Yaf_Registry::set("storage_cloud_db", new MySQL(
//            $storage_cloud->host,
//            $storage_cloud->port,
//            $storage_cloud->username,
//            $storage_cloud->password,
//            $storage_cloud->default,
//            $storage_cloud->charset
//        ));

//
//        $db = $config->get("app_db");
//        Yaf_Registry::set("app_db",  new MySQL(
//            $db->host,
//            $db->port,
//            $db->username,
//            $db->password,
//            $db->default,
//            $db->charset
//        ));

//        $dbdata = $config->get("dbdata");
//        Yaf_Registry::set("dbdata", new MySQL(
//            $dbdata->host,
//            $dbdata->port,
//            $dbdata->username,
//            $dbdata->password,
//            $dbdata->default,
//            $dbdata->charset
//        ));
//
//        $bank_db = $config->get("bank_db");
//        Yaf_Registry::set("bank_db", new MySQL(
//            $bank_db->host,
//            $bank_db->port,
//            $bank_db->username,
//            $bank_db->password,
//            $bank_db->default,
//            $bank_db->charset
//        ));

//        $gyerp_db = $config->get("gyerp_db");
//        Yaf_Registry::set("gyerp_db", new MySQL(
//            $gyerp_db->host,
//            $gyerp_db->port,
//            $gyerp_db->username,
//            $gyerp_db->password,
//            $gyerp_db->default,
//            $gyerp_db->charset
//        ));

        // $dbguoyie = $config->get("dbguoyie");
        // Yaf_Registry::set("dbguoyie",  new MySQL(
        //     $dbguoyie->host,
        //     $dbguoyie->port,
        //     $dbguoyie->username,
        //     $dbguoyie->password,
        //     $dbguoyie->default,
        //     $dbguoyie->charset
        // ));


        /*       $pa_db = $config->get("pa_db");
               Yaf_Registry::set("pa_db",  new MySQL(
                   $pa_db->host,
                   $pa_db->username,
                   $pa_db->password,
                   $pa_db->default,
                   $pa_db->charset
               ));*/
        /*
        $remote = $config->get("remote");
        Yaf_Registry::set("remote",  new MySQL(
            $remote->host,
            $remote->username,
            $remote->password,
            $remote->default,
            $remote->charset
        ));
         */

        /*$redis = $config->get("redis");

        if(isset($redis->pwd)){
            $options = [
                'parameters' => [
                    'password' => $redis->pwd,
                ],
            ];
            $client = new Predis\Client([
                'scheme' => 'tcp',
                'host'   => $redis->host,
                'port'   => $redis->port
            ],$options);
        }
        else {
            $client = new Predis\Client([
                'scheme' => 'tcp',
                'host'   => $redis->host,
                'port'   => $redis->port
            ]);
        }

        Yaf_Registry::set("mc",$client);
*/

        $session = Yaf_Session::getInstance();
        if ($session->has(SSN_PASS)) {
            Yaf_Registry::set(SSN_PASS, $session->get(SSN_PASS));
        } else {
            Yaf_Registry::set(SSN_PASS, false);
        }
        if ($session->has(SSN_INFO)) {
            Yaf_Registry::set(SSN_INFO, $session->get(SSN_INFO));
        } else {
            Yaf_Registry::set(SSN_INFO, array('id' => 0, 'name' => 'guest', 'dept' => '', 'sa' => 0));
        }
//
//        $carpc = $config->get("carpc");
//        Yaf_Registry::set("carpc", $carpc);

        $rpc = $config->get("rpc");
        Yaf_Registry::set("rpc", $rpc);
        
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