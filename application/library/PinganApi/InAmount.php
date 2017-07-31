<?php
/**
 * 入金接口（银行发起请求）
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/4 0004
 * Time: 11:42
 */
include 'PinganApi.php';
class InAmount extends PinganApi{

    private $supacctid=null;                    //资金汇总账号
    private $custacctid=null;                   //子账户账号
    private $tranamount=null;                   //入金金额
    private $inacctid=null;                     //入金账号
    private $inacctidname=null;                //入金账户名称
    private $ccycode=null;                      //币种
    private $acctdate=null;                     //会计日期:即银行主机记账日期
    private $reserve=null;                      //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'inacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'inacctidname'=>array('required' => true, 'min' => 1, 'max' => 120),
        'ccycode'=>array('required' => true, 'min' => 1, 'max' => 3),
        'acctdate'=>array('required' => true, 'min' => 1, 'max' => 8),
        'reserve'=>array('required' => true, 'min' => 1, 'max' => 120)
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