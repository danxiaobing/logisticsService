<?php
/**
 * 出入金账户维护接口（银行发起请求）
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/4 0004
 * Time: 11:08
 */
include 'PinganApi.php';
class OutInAccount extends PinganApi{

    private $funcflag=1;                          //功能标志:1:新增 2：修改
    private $custacctid=null;                    //子账户账号
    private $relatedacctid=null;                //出/入金账号
    private $acctflag=3;                         //账号性质:3：出金账号&入金账号
    private $trantype=1;                         //转账方式:1：本行
    private $acctname=null;                     //账号名称
    private $bankcode=null;                     //联行号（本行为分行号）
    private $bankname=null;                     //开户行名称
    private $address=null;                      //付款人/收款人地址
    private $oldrelatedacctid=null;           //原出入金账号（若FuncFlag为1时为空）
    private $reserve=null;                      //保留域（此处为“交易网会员代码”）

    private $validate=array(
        'funcflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'relatedacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'acctflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'trantype'=>array('required' => true, 'min' => 1, 'max' => 1),
        'acctname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'bankcode'=>array('required' => false, 'min' => 1, 'max' => 12),
        'bankname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'address'=>array('required' => true, 'min' => 0, 'max' => 120),
        'oldrelatedacctid'=>array('required' => false, 'min' => 0, 'max' => 32),
        'reserve'=>array('required' => true, 'min' => 0, 'max' => 120)
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