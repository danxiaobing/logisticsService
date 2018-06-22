<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/6 11:12
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 收付款单-操作日志表
 */
class Payment_LogModel
{
    /**
     * @var string  默认的表名
     */
    public static $tableName = 'payment_log';
    /**
     * @var MySQL
     */
    public $dbh = null;

    //静态变量保存全局实例
    /**
     * @var null
     */
    private static $_instance = null;

    //私有构造函数，防止外界实例化对象
    private function __construct()
    {
    }

    //私有克隆函数，防止外办克隆对象
    private function __clone()
    {
    }

    /**
     * @return null|Payment_LogModel
     * @throws Yaf_Exception
     * 静态方法，单例统一访问入口
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance) || isset (self::$_instance)) {
            self::$_instance = new self ();
            if (Yaf_Registry:: get("db") instanceof MySQL) {
                self::$_instance->dbh = Yaf_Registry:: get("db");
            } else {
                throw new Yaf_Exception("db配置不对");
            }
        }

        return self::$_instance;
    }

    /**
     * @param $params
     * @return bool
     */
    public function addPaymentLog($params)
    {
        $params['logtime'] = '=NOW()';
        return $this->dbh->insert(self::$tableName, $params);
    }

}