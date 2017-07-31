<?php
/**
 * @author  James
 * @date    2011-08-01 15:00
 * @version $Id$
 */
Trait TraitHprose
{
    protected $allowMethodList  =   '';
    protected $crossDomain      =   true;
    protected $P3P              =   true;
    protected $get              =   true;
    protected $debug            =   true;

    /**
     * 架构函数
     * @access public
     */
    public function initRpc() {
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
        //导入类库
        Yaf_Loader::import('Hprose.php');

        //实例化HproseHttpServer
        $server     =    new Hprose\Http\Server();
        if($this->allowMethodList){
            $methods    =   $this->allowMethodList;
        }else{
            $tmp_methods    =   get_class_methods($this);
            #$tmp_methods    =   array_diff($tmp_methods, array('__construct','__call','_initialize', '__destruct', 'init', 'indexAction'));
            
            $methods = array();
            
            if(count($tmp_methods) > 0){
               foreach($tmp_methods as $method){
                    if(preg_match("/Func$/", $method)){
                         $methods[] = $method;
                    }
               }
            }
        }

        $server->addMethods($methods,$this);
        if($this->debug) {
            $server->setDebugEnabled(true);
        }
        // Hprose设置
        $server->setCrossDomainEnabled($this->crossDomain);
        $server->setP3PEnabled($this->P3P);
        $server->setGetEnabled($this->get);
        // 启动server
        $server->start();

        exit;
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args){}
}
 