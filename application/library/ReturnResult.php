<?php

/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/12 13:06
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * 接口返回的基础类
 */
class ReturnResult
{
    /**
     * @var int 返回的code
     */
    public $code;
    /**
     * @var string 返回的说明
     */
    public $message;

    /**
     * @var null 返回的数据
     */
    public $data;

    /**
     * ReturnResult constructor.
     * @param int $code
     * @param string $message
     */
    public function __construct($code = 200, $message = 'success')
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param int $code
     * @param string $message
     * @return ReturnResult
     * 失败
     */
    public static function failed($code = StatusCode::CLIENT_ERROR_CODE,$message = StatusCode::CLIENT_ERROR_STRING){
        $msg =  new ReturnResult($code,$message);
        $msg->data = null;
        return $msg;
    }

    /**
     * @param $data
     * @return ReturnResult
     * 成功
     */
    public static function success($data = null){
        $msg =  new ReturnResult();
        $msg->data = $data;
        return $msg;
    }

    /**
     * @return string 转换为JSON格式
     */
    public function toJson(){
        return json_encode($this,JSON_UNESCAPED_UNICODE);
    }
    /**
     * @return array 转换为array
     */
    public function toArray(){
        return (array)$this;
    }
    /**
     * @return bool
     * 是否成功
     */
    public function isOk(){
        return $this->code == StatusCode::SUCCESS_CODE;
    }
}