<?php
/**
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/2 0002
 * Time: 13:11
 */
class Api{

    private $apiname=null;
    private $method;

    function __construct($apiname) {
        $this->apiname=$apiname;
    }

    public function setMethod($method){
        $this->method=$method;
        return $this;
    }

    public function goHandle($params){
        $md=$this->getClass();
        return new $md($params);
    }

    private function getClass(){
        include ($this->apiname.'/'.$this->method.'.php');
        return $this->method;
    }
}