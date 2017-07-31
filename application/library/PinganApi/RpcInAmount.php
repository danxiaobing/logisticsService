<?php
/**
 * 入金（平台发起请求）
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/4 0004
 * Time: 18:06
 */
include 'PinganApi.php';
class RpcInAmount extends PinganApi{

    private $supacctid=null;                    //资金汇总账号
    private $custacctid=null;                   //子账户账号
    private $thirdcustid=null;                  //交易网会员代码（ID）
    private $idtype=null;                       //会员证件类型
    private $idcode=null;                       //会员证件号码
    private $tranamount=null;                   //入金金额
    private $inacctid=null;                     //入金账号
    private $inacctidname=null;                //入金账户名称
    private $ccycode=null;                      //币种
    private $reserve=null;                      //保留域


    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'idtype'=>array('required' => true, 'min' => 1, 'max' => 2),
        'idcode'=>array('required' => true, 'min' => 1, 'max' => 20),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'inacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'inacctidname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'ccycode'=>array('required' => true, 'min' => 1, 'max' => 3),
        'reserve'=>array('required' => false, 'min' => 1, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'ThirdLogNo'=>'llllllllllllllllllll',  //交易网流水号
        'Reserve'=>'ksssssssdf'                  //保留域
    );

    //获得默认数据字段配置
    public function getValidate(){
        return $this->validate;
    }

    //返回最终结果
    public function result(){
        if($this->validate()){
            //签到
            $this->sign(1);
            //请求接口发送数据
            // code...
            return json_encode($this->respones);
            //签退
            $this->sign(2);
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