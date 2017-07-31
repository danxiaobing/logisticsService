<?php
/**
 * @ 查银行端会员资金台帐余额【1010】
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/10 0010
 * Time: 13:59
 */
include 'PinganApi.php';
class RpcSelectAmount extends PinganApi{

    private $supacctid=null;            //资金汇总账号
    private $thirdcustid=null;         //交易网会员代码
    private $custacctid=null;          // 子账户
    private $selectflag=1;             //查询标志 1：全部 2：普通会员子账户 3：功能子账户（利息、手续费、清收子账户）
    private $pagenum=null;            //第几页 起始值为1，每次最多返回20条记录，第二页返回的记录数为第21至40条记录，第三页为41至60条记录，顺序均按照建立时间的先后
    private $reserve=null;            //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'thirdcustid'=>array('required' => false, 'min' => 0, 'max' => 32),
        'custacctid'=>array('required' => false, 'min' => 0, 'max' => 32),
        'selectflag'=>array('required' => true, 'min' => 1, 'max' => 1),
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
                'CustAcctId'=>'CustAcctId',         //子账户
                'CustFlag'=>'1',                        //子账户性质（1：虚拟账号，2：实体账号）
                'CustType'=>'1',                       //子账户属性， 1：普通会员子账户 2：挂账子账户  3：手续费子账户 4：利息子账户 6：清收子账户
                'CustStatus'=>'1',                      //子账户状态,1：正常  2：已销户
                'ThirdCustId'=>'ThirdCustId',       //交易网会员代码(ID)
                'MainAcctId'=>'MainAcctId',          //上级监管账号
                'CustName'=>'CustName',             //会员名称
                'TotalAmount'=>'999999',            //账户总余额
                'TotalBalance'=>'999999',           //账户可用余额
                'TotalFreezeAmount'=>'0',           //账户总冻结金额
                'TranDate'=>'TranDate',             //开户日期或修改日期
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