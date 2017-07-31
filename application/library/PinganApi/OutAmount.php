<?php
/**
 * 出金接口（银行发起请求）
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/4 0004
 * Time: 14:08
 */
include 'PinganApi.php';
class OutAmount extends PinganApi{

    private $tranwebname=null;                  //交易网名称
    private $thirdcustid=null;                  //交易网会员代码(ID)
    private $custacctid=null;                   //子账户账号
    private $custname=null;                     //子账户名称
    private $supacctid=null;                    //资金汇总账号
    private $outacctid=null;                    //出金账号
    private $outacctidname=null;               //出金账户名称
    private $ccycode=null;                      //币种，默认为RMB
    private $tranamount=null;                   //出金金额
    private $handfee=null;                      //转账手续费
    private $feeoutcustid=null;                 //支付手续费子账号
    private $reserve=null;                      //保留域

    private $validate=array(
        'tranwebname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'outacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'outacctidname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'ccycode'=>array('required' => true, 'min' => 1, 'max' => 3),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'handfee'=>array('required' => true, 'min' => 1, 'max' => 15),
        'feeoutcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
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
