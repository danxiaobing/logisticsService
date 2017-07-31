<?php
/**
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/4 0004
 * Time: 16:19
 */
include 'PinganApi.php';
class CheckResult extends PinganApi{

    private $supacctid=null;                    //资金汇总账号
    private $funcflag=null;                     //功能标志:1：支付复核
    private $checkresult=null;                  //复核结果:Y：复核通过
    private $custacctid=null;                   //子账户（复核人的）
    private $thirdcustid=null;                  //会员代码（ID）（复核人的）
    private $tranamount=null;                   //出金金额
    private $handfee=null;                      //转账手续费
    private $ccycode=null;                      //币种，默认为RMB
    private $thirdhtid=null;                    //订单号
    private $payserialno=null;                  //原支付指令号
    private $note=null;                          //备注
    private $reserve=null;                      //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'funcflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'checkresult'=>array('required' => true, 'min' => 1, 'max' => 1),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'handfee'=>array('required' => true, 'min' => 1, 'max' => 15),
        'ccycode'=>array('required' => true, 'min' => 1, 'max' => 3),
        'thirdhtid'=>array('required' => true, 'min' => 1, 'max' => 30),
        'payserialno'=>array('required' => true, 'min' => 1, 'max' => 20),
        'note'=>array('required' => false, 'min' => 1, 'max' => 120),
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
            //相应的数据插入数据库
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
