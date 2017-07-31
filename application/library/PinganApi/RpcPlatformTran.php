<?php
/**
 * @ 平台操作支付接口  1331
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/5 0005
 * Time: 14:43
 */
include 'PinganApi.php';
class RpcPlatformTran extends PinganApi{

    private $supacctid=null;                     //资金汇总账号
    private $funcflag=1;                          // 1：代理复核 2：强制支付
    private $payserialno=null;                  //支付指令号（根据该字段判断是否指令重复）
    private $thirdhtid=null;                    //支付订单号
    private $payamount=null;                    //支付金额
    private $payfee=null;                        //手续费
    private $note=null;                          //备注
    private $reserve=null;                       //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'funcflag'=>array('required' => true, 'min' => 1, 'max' => 1),
        'payserialno'=>array('required' => true, 'min' => 1, 'max' => 20),
        'thirdhtid'=>array('required' => true, 'min' => 1, 'max' => 30),
        'payamount'=>array('required' => true, 'min' => 1, 'max' => 15),
        'payfee'=>array('required' => true, 'min' => 1, 'max' => 15),
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