<?php
/**
 * 会员开销户接口（银行发起请求）
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/2 0002
 * Time: 13:38
 */
include 'PinganApi.php';
class OpenAccount extends PinganApi{

    private $funcflag=null;                     //功能标志:1:开户 3：销户
    private $supacctid=null;                    //资金汇总账号
    private $custacctid=null;                   //子账户账号
    private $custname=null;                     //会员名称
    private $thirdcustid=null;                  //交易网会员代码
    private $idtype=null;                       //子账户证件类型
    private $idcode=null;                       //子账户证件号码
    private $custflag=null;                     //子账户性质:1：虚拟户
    private $totalamount=null;                  //子账户开户时的初始总金额，默认为0
    private $totalbalance=null;                 //子账户开户时的初始可用余额，默认为0
    private $totalfreezeamount=null;           //子账户开户时的初始冻结金额，默认为0
    private $reserve=null;                      //保留域

    private $validate=array(
        'funcflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'idtype'=>array('required' => true, 'min' => 1, 'max' => 2),
        'idcode'=>array('required' => true, 'min' => 1, 'max' => 20),
        'custflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'totalamount'=>array('required' => false, 'min' => 0, 'max' => 15),
        'totalbalance'=>array('required' => false, 'min' => 0, 'max' => 15),
        'totalfreezeamount'=>array('required' => false, 'min' => 0, 'max' => 15),
        'reserve'=>array('required' => false, 'min' => 0, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'ThirdLogNo'=>'llllllllllllllllllll',  //交易网流水号
        'Reserve'=>'ksssssssdf'                     //保留域
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