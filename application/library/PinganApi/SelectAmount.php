<?php
/**
 * @ 查交易网端会员管理账户余额【1019】
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/10 0010
 * Time: 14:39
 */
include 'PinganApi.php';
class SelectAmount extends PinganApi{

    private $supacctid=null;                    //资金汇总账号
    private $thirdcustid=null;                  //交易网会员代码
    private $custacctid=null;                   //子账户账号
    private $reserve=null;                      //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'reserve'=>array('required' => false, 'min' => 0, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'CustAcctId'=>'CustAcctId',                     //子账户
        'ThirdCustId'=>'ThirdCustId',                  //交易网会员代码(ID)
        'CustName'=>'CustName',                         //会员名称
        'TotalAmount'=>'100000',                         //账户总余额:以分为单位，例如100.01元，则填：10001（此处特殊，其它接口的金额都以元为单位）
        'TotalBalance'=>'100000',                       //账户可用余额:以分为单位，例如100.01元，则填：10001（此处特殊，其它接口的金额都以元为单位）
        'TotalFreezeAmount'=>'0',                       //账户总冻结金额:以分为单位，例如100.01元，则填：10001（此处特殊，其它接口的金额都以元为单位）
        'TranDate'=>'20160810',                         //开户日期或修改日期
        'Reserve'=>'Reserve'                            //保留域
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