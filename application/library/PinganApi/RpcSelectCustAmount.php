<?php
/**
 * @ 查会员出入金账号的银行余额【1020】
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/10 0010
 * Time: 14:55
 */
include 'PinganApi.php';
class RpcSelectAmount extends PinganApi{

    private $supacctid=null;            //资金汇总账号
    private $custacctid=null;          // 子账户
    private $thirdcustid=null;         //交易网会员代码
    private $custname=null;             //会员名称
    private $acctno=null;            //出入金账号：用于查询的账号或卡号
    private $reserve=null;            //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'thirdcustid'=>array('required' => false, 'min' => 1, 'max' => 32),
        'custname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'acctno'=>array('required' => true, 'min' => 1, 'max' => 32),
        'reserve'=>array('required' => false, 'min' => 0, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'Balance'=>'100000',             //银行可用余额
        'Reserve'=>'Reserve',            //保留域
    );

    //获得默认数据字段配置
    public function getValidate(){
        return $this->validate;
    }

    //返回最终结果
    public function result(){
        if($this->validate()){
            //请求接口发送数据
            // code...
            return json_encode($this->respones);
        }else{
            return $this->getError();
        }
    }

    /**
     * 构造方法兼初始化
     * @author : dujiangjiang
     * @vesion : 2.0.0.0
     */
    public function __construct($argv) {
        foreach ($argv as $key => $value) {
            $this->$key = $value;
        }
    }

}