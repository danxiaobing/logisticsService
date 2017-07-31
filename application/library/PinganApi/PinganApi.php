<?php
/**
 * Created by PhpStorm.
 * User: Dujiangjiang
 * Date: 2016/8/2 0002
 * Time: 14:32
 */
class PinganApi{

    //由于接口文档写的不是很详细，很多东西不明白，暂时先写成这样，等有对接人员时再修改

    private $server_host="127.0.0.1";
    private $server_port="7072";
    private $linkcode = "";
    private $error='error';


    //以下字段长度均为最大支持长度

    //通讯报文头默认数据
    private $shead = array(
        'type'=>'A001',         //报文类别（4位，必填）
        'target'=>'03',           //目标系统（2位，必填）
        'encrypt'=>'01',           //报文编码（2位，必填）
        'protocol'=>'01',           //通讯协议（2位，必填）,01:tcpip 缺省 02：http
        'linkcode'=>'',              //企业银企直连标准代码（20位，必填）
        'length'=>'200',          //报文数据长度=122+业务报文体（10位，必填）
        'transaction'=>'000000',      //通信报文统一交易码:000000（6位，必填）
        'counterid'=>'12345',         //操做员代码（5位，可选）
        'servtype'=>'01',           //服务类型（2位，可选）  01-请求；02-应答
        'trandate'=>'20160802',      //交易日期（8位，必填），格式（yyyymmdd）
        'trantime'=>'145959',        //交易时间（6位，必填），格式（hhmmss)
        'thirdlogno'=> '',                 //请求方系统流水号（20位，必填），唯一标识一笔交易（企业随机生成唯一流水号，可与业务报文头的流水号一致）
        'rspcode'=>'000000',           //返回码（6位，可选）
        'rspmsg'=>'交易成功',         //返回描述（20位，可选）
        'conflag'=>'0',                //后续包标志（1位，可选），0-结束包
        'reqnum'=>'1',                //请求次数（3位，可选）
        'signlogo'=>'0',                //签名标识（1位，可选），填0，企业不管，由银行客户端完成
        'signtype'=>'1',                //签名数据包格式（1位，可选），填1，企业不管，由银行客户端完成
        'signalgr'=>'',                 //签名算法（12位，可选）
        'signlen'=>'0',                //签名数据长度（10位，必填）
        'attanum'=>'0'                 //附件数目（1位，必填）
    );

    //业务报文头默认数据
    private $bhead = array(
        'tranfunc'=>'1234',             //交易类型（4位，必填）
        'servtype'=>'01',               //服务类型（2位，必填）  01-请求；02-应答
        'maccode'=>'',                  //MAC码（16位，必填）
        'trandate'=>'20160802',          //交易日期（8位，必填），格式（yyyymmdd）
        'trantime'=>'145959',        //交易时间（6位，必填），格式（hhmmss)
        'rspcode'=>'000000',        //应答码（6位，必填）：交易发起方初始填入”999999”，应答码表示一次业务交易的成功与否，000000表示成功，其余表示失败；
        'rspmsg'=>'交易成功',       //应答码描述（42位，必填）
        'conflag'=>'0',                 //后续包标志（1位，必填）：0结束包,1还有后续包；
        'length'=>'300',                //报文长度（8位，必填）
        'counterid'=>'12345',         //操做员代码（5位，必填）
        'thirdlogno'=>'',                //请求方系统流水号（20位，必填），唯一标识一笔交易（企业随机生成唯一流水号，可与业务报文头的流水号一致）
        'qydm'=>'asdf'                  //企业代码（4位，必填），由银行指定，固定值
    );

    //通讯报文头处理
    public function setSignHeader($params){
        $head=array();
        return $head;
    }

    //业务报文头处理
    public function setBusinessHeader($params){
        $head=array();
        return $head;
    }

    //报文拼接处理结果
    public function getHeader($params){
        $sh=$this->setSignHeader($params);
        $bh=$this->setBusinessHeader($params);
        return;
    }

    //发送请求
    private function send($params){
        $sign=$this->getHeader($params);
        //发送接口请求，暂时留下待处理
    }

    //获得错误信息
    public function getError(){
        return $this->error;
    }

    /*
     * @ 接口参数基础验证
     * */
    public function validate(){

        $validate=$this->getValidate();

        if(count($validate) > 0){
            foreach ($validate as $key => $value) {
                if ($value['required'] == true && !isset($this->$key)) {
                    $this->error = $key . ' is not exists!';
                    return false;
                }

                if ($value['required'] == false && !isset($this->$key)) {
                    continue;
                }

                if (isset($value['min']) && $value['min'] > strlen($this->$key)) {
                    $this->error = $key . ' length < ' . $value['min'];
                    return false;
                }

                if (isset($value['max']) && $value['max'] < strlen($this->$key)) {
                    $this->error = $key . ' length > ' . $value['max'];
                    return false;
                }
            }
        }else{
            $this->error = "validate is empty";
            return false;
        }
        return true;
    }

    /*
     * 签到、签退接口  1330
     * @ $param   1：签到，2：签退
     * */
    public function sign($param=1){
        $argv=array(
            'FuncFlag'=>$param,
            'TxDate'=>date("Ymd"),
            'Reserve'=>''
        );
        $res=$this->send($argv);
        return $res;
    }

}