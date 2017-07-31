<?php
/**
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/4 0004
 * Time: 20:20
 */
include 'PinganApi.php';
class RpcOutAmount extends PinganApi{

    private $tranwebname=null;                    //交易网名称
    private $thirdcustid=null;                  //交易网会员代码（ID）
    private $idtype=null;                       //会员证件类型
    private $idcode=null;                       //会员证件号码
    private $tranouttype=1;                     //出金类型：1：会员出金
    private $custacctid=null;                   //子账户账号
    private $custname=null;                     //会员名称
    private $supacctid=null;                    //资金汇总账号
    private $trantype=1;                         //转账方式：1：行内转账
    private $outacctid=null;                    //出金账号，即收款账户
    private $outacctidname=null;                //出金账户名称,与会员名称一致
    private $outacctidbankname=null;            //出金账号开户行名（填“平安银行”）
    private $outacctidbankcode=null;            //出金账号开户联行号
    private $address=null;                       //出金账号开户行地址
    private $ccycode=null;                      //币种（默认为RMB）
    private $tranamount=null;                   //申请出金金额（不包括转账手续费）
    private $feeoutcustid=null;                 //支付转账手续费的子账户（预留字段，无实际作用）
    private $reserve=null;                      //保留域


    private $validate=array(
        'tranwebname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'idtype'=>array('required' => true, 'min' => 1, 'max' => 2),
        'idcode'=>array('required' => true, 'min' => 1, 'max' => 20),
        'tranouttype'=>array('required' => true, 'min' => 1, 'max' => 2),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'trantype'=>array('required' => true, 'min' => 1, 'max' => 1),
        'outacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'outacctidname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'outacctidbankname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'outacctidbankcode'=>array('required' => false, 'min' => 0, 'max' => 12),
        'address'=>array('required' => false, 'min' => 0, 'max' => 120),
        'ccycode'=>array('required' => false, 'min' => 0, 'max' => 3),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'feeoutcustid'=>array('required' => false, 'min' => 0, 'max' => 32),
        'reserve'=>array('required' => false, 'min' => 0, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'FrontLogNo'=>'llllllllllllllllllll',   //前置流水号
        'HandFee'=>'0.00',                           //转账手续费
        'FeeOutCustId'=>'123456',                    //支付手续费子账户（预留字段，无实际作用）
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