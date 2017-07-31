<?php
/**
 * @ 查询时间段会员交易流水信息【1324】
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/10 0010
 * Time: 15:09
 */
include 'PinganApi.php';
class RpcSelectThirdLog extends PinganApi{

    private $supacctid=null;            //资金汇总账号
    private $origthirdlogno=null;       //交易网流水号(若为空则返回全部)
    private $begindate=null;            // 开始日期
    private $enddate=null;              //结束日期
    private $pagenum=null;             //第几页,起始值为1，每次最多返回20条记录，第二页返回的记录数为第21至40条记录，第三页为41至60条记录，顺序均按照建立时间的先后
    private $reserve=null;            //保留域

    private $validate=array(
        'supacctid'=>array('required' => true, 'min' => 1, 'max' => 32),
        'origthirdlogno'=>array('required' => false, 'min' => 0, 'max' => 20),
        'begindate'=>array('required' => false, 'min' => 1, 'max' => 8),
        'enddate'=>array('required' => true, 'min' => 1, 'max' => 8),
        'pagenum'=>array('required' => true, 'min' => 1, 'max' => 6),
        'reserve'=>array('required' => false, 'min' => 0, 'max' => 120)
    );

    //默认返回结果
    private $respones=array(
        'TotalCount'=>'TotalCount',             //总记录数
        'BeginNum'=>'BeginNum',                 //起始记录
        'LastPage'=>'1',                        //是否结束包 0：否  1：是
        'RecordNum'=> 20,                       //本次返回流水笔数,重复次数（一次最多返回20条记录）
        'data'=>array(                              //信息数组
            array(
                'ThirdLogNo'=>'ThirdLogNo',         //交易网流水号
                'FrontLogNo'=>'FrontLogNo',         //银行前置流水号
                'TranFlag'=>'1',                      //1：申请支付 2：冻结 3：解冻 4：收费 5：退费6：会员支付到市场 7：市场支付到会员 8：确认支付 9：可用直接支付 10：撤销支付 11：代理确认支付 12：强制支付 13：冻结直接支付 14：冻结收费 15：会员冻结支付到市场16：子账户间可用支付17：子账户间冻结支付
                'TranStatus'=>'0',                    //0：成功 1:失败（交易网流水号不为空时才返回）2：异常（交易网流水号不为空时才返回，异常是中间状态，需等待一段时间（5-10分钟）后重新查询结果）
                'TranAmount'=>'888888',              //交易金额
                'OutCustAcctId'=>'OutCustAcctId',  //转出子账户，即付款方
                'OutThirdCustId'=>'OutThirdCustId',//转出会员代码
                'InCustAcctId'=>'InCustAcctId',     //转入子账户，即收款方
                'InThirdCustId'=>'InThirdCustId',   //转入会员代码
                'TranDate'=>'TranDate',             //交易日期
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