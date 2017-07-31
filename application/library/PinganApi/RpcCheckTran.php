<?php
/**
 * @ 子账户复核支付接口（平台发起请求）
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/5 0005
 * Time: 14:25
 */
include 'PinganApi.php';
class RpcCheckTran extends PinganApi{

    private $supacctid=null;                    //资金汇总账号
    private $funcflag=1;                         // 1：申请支付 2：撤销支付
    private $outcustacctid=null;                //转出子账户账号
    private $outthirdcustid=null;               //转出会员代码（ID）
    private $incustacctid=null;                 //转入子账户账号
    private $inthirdcustid=null;                //转入会员代码（ID）
    private $tranamount=null;                   //支付金额
    private $handfee=null;                      //手续费
    private $ccycode=null;                      //币种
    private $payserialno=null;                  //支付指令号（根据该字段判断是否指令重复）
    private $thirdhtid=null;                    //支付订单号
    private $thirdhtcont=null;                   //支付订单内容
    private $note=null;                           //备注
    private $reserve=null;                      //保留域


    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'funcflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'outcustacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'outthirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'incustacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'inthirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'handfee'=>array('required' => true, 'min' => 1, 'max' => 15),
        'ccycode'=>array('required' => true, 'min' => 1, 'max' => 3),
        'payserialno'=>array('required' => true, 'min' => 1, 'max' => 20),
        'thirdhtid'=>array('required' => true, 'min' => 1, 'max' => 30),
        'thirdhtcont'=>array('required' => false, 'min' => 1, 'max' => 500),
        'note'=>array('required' => false, 'min' => 1, 'max' => 120),
        'reserve'=>array('required' => false, 'min' => 1, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'FrontLogNo'=>'llllllllllllllllllll',  //交易网流水号
        'Reserve'=>'ksssssssdf'                  //保留域
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