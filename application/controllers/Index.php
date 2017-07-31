<?php
use OSS\Core\OssException;
use OSS\OssClient;
use Hprose\Client;

class IndexController extends Yaf_Controller_Abstract
{
    private $code = '0000';
    private $msg = 'success';
    private $data = array();

    /**
     * IndexController::init()
     *
     * @return void
     */
    public function init()
    {
        # parent::init();
    }

    /**
     * 显示整个后台页面框架及菜单
     *
     * @return string
     */
    public function IndexAction()
    {
        echo "service Index OK ";
    }
}
