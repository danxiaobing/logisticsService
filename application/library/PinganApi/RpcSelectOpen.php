<?php
/**
 * @ 查时间段会员开销户信息【1016】
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/10 0010
 * Time: 14:11
 */
include 'PinganApi.php';
class RpcSelectOpen extends PinganApi{

    private $supacctid=null;            //资金汇总账号
    private $begindate=null;            //开始日期
    private $enddate=null;              // 结束日期
    private $pagenum=null;            //第几页 起始值为1，每次最多返回20条记录，第二页返回的记录数为第21至40条记录，第三页为41至60条记录，顺序均按照建立时间的先后
    private $reserve=null;            //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'begindate'=>array('required' => true, 'min' => 1, 'max' => 8),
        'enddate'=>array('required' => true, 'min' => 1, 'max' => 8),
        'pagenum'=>array('required' => true, 'min' => 1, 'max' => 6),
        'reserve'=>array('required' => false, 'min' => 1, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'TotalCount'=>'TotalCount',             //总记录数
        'BeginNum'=>'BeginNum',                 //起始记录
        'LastPage'=>'1',                        //是否结束包 0：否  1：是
        'RecordNum'=> 20,                       //本次返回流水笔数,重复次数（一次最多返回20条记录）
        'data'=>array(                              //信息数组
            array(
                'FrontLogNo'=>'FrontLogNo',         //银行前置流水号
                'UserStatus'=>'1',                  //交易状态（1：开户 2：销户 3：待确认）
                'CustAcctId'=>'CustAcctId',         //子账户
                'CustFlag'=>'1',                        //子账户性质（1：虚拟账号）
                'CustName'=>'CustName',             //会员名称
                'ThirdCustId'=>'ThirdCustId',       //交易网会员代码(ID)
                'TranDate'=>'TranDate',             //交易日期
                'CounterId'=>'CounterId'            //操作柜员号
            ),
            //……
        ),
        'Reserve'=>'Reserve',                   //保留域
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