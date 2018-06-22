<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/6 11:12
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 收付款单-附件表
 */
class Payment_FilesModel
{
    /**
     * @var string  默认的表名
     */
    public static $tableName = 'payment_files';
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
     * @return null|Payment_FilesModel
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
     * @param array $params 收付款单-附件表参数
     * @return mixed
     * 添加收付款单-附件表
     */
    public function addPaymentFiles($params)
    {
        $params['uploadtime'] = '=NOW()';
        return $this->dbh->insert(self::$tableName, $params);
    }

    /**
     * @param array $params 列表数据
     * 例子：$params['paymentno'=>111,'filesList'=>[[],[],[]]]
     * @return bool
     * @throws Yaf_Exception
     * 添加收付款单-附件表列表
     */
    public function addPaymentListFiles($params){
        $paymentno = isset($params['paymentno'])?$params['paymentno']:null;
        if($paymentno == null){
            throw new Yaf_Exception('添加收付款单文件时，收付款单号不能为空');
        }
        $filesList = isset($params['filesList'])?$params['filesList']:null;
        if($filesList == null || !is_array($filesList)){
            throw new Yaf_Exception('添加收付款单文件时，没有找到文件列表');
        }

        foreach ($filesList as $key=>$files){
            $files['paymentno'] = $paymentno;
            try{
                $this->addPaymentFiles($files);
            }catch (Exception $exception){
                throw new Yaf_Exception($exception->getMessage());
            }
        }
        return true;
    }

    /**
     * @param $paymentno
     * @param $data
     * @return bool
     * 更新收付款单-附件表
     */
    public function updatePaymentFiles($paymentno, $data)
    {
//        $data['uploadtime'] = '=NOW()';
        return $this->dbh->update(self::$tableName, $data, "paymentno = '" . $paymentno."'");
    }

    public function deletePaymentFiles($paymentno){
        return $this->dbh->delete(self::$tableName,"paymentno = '".$paymentno."'");
    }

}