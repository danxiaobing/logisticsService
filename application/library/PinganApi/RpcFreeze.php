<?php
/**
 * @ 子账户冻结解冻接口 1029
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/5 0005
 * Time: 15:07
 */
include 'PinganApi.php';
class RpcPlatformTran extends PinganApi{

    private $supacctid=null;                     //资金汇总账号
    private $funcflag=1;                          // 1：冻结 2：解冻
    private $custacctid=null;                   //子账户
    private $thirdcustid=null;                  //会员代码（ID）
    private $tranamount=null;                   //交易金额
    private $ccycode=null;                       //币种
    private $thirdhtid=null;                    //订单号
    private $note=null;                          //备注
    private $reserve=null;                      //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'funcflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'custacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'thirdcustid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'tranamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'ccycode'=>array('required' => true, 'min' => 1, 'max' => 3),
        'thirdhtid'=>array('required' => false, 'min' => 0, 'max' => 30),
        'note'=>array('required' => false, 'min' => 0, 'max' => 120),
        'reserve'=>array('required' => false, 'min' => 0, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'FrontLogNo'=>'llllllllllllllllllll',  //前置流水号
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