<?php
/**
 * Entry Name: ec_service_order
 * LastModified: 2018/3/12 13:54
 * Author: Wang HuiHui <wanghuihui@chinayie.com>
 * code和message常用值
 */

class StatusCode
{
    const SUCCESS_CODE  = 200;
    const SUCCESS_STRING  = 'success';

    const CLIENT_ERROR_CODE  = 300;
    const CLIENT_ERROR_STRING  = '请重新操作！';

    const CLIENT_ERROR_ACCESS_TOO_FREQUENTLY_CODE = 40000002; //接口访问太频繁
    const CLIENT_ERROR_ACCESS_TIMEOUT = 40000003;         //接口访问超时

    const CLIENT_ERROR_ACCESS_DENY_CODE  = 40000004;    //访问拒绝
    const CLIENT_ERROR_ACCESS_DENY_STRING  = '访问拒绝';    //访问拒绝

    const CLIENT_EMPTY_PARAMETER_CODE = 40000006; //参数为空
    const CLIENT_EMPTY_PARAMETER_STRING = '参数为空'; //参数为空

    const CLIENT_ILLEGAL_PARAMETER_CODE  = 40000007;    //参数非法
    const CLIENT_ILLEGAL_PARAMETER_STRING  = '参数非法';    //参数非法

    const CLIENT_DATA_NOT_EXISTS_CODE = 40000008; //数据不存在
    const CLIENT_DATA_NOT_EXISTS_STRING = '数据不存在'; //数据不存在

    const CLIENT_NOT_LOGIN_CODE = 40000009;//未登录
    const CLIENT_NOT_LOGIN_STRING = '未登录';//未登录

    const CLIENT_DECODE_ERROR_CODE = 40000010;// 解码错误
    const CLIENT_PARAM_FORMAT_ERROR_CODE = 40000011;//格式化错误
    const CLIENT_PARAM_VALIDATE_ERROR_CODE = 40000012;//参数校验失败

    const SERVER_ERROR_CODE  = 50000000;    //服务器常用错误码
    const SERVER_ERROR_STRING  = '服务器内部错误';    //服务器常用错误码


    const SERVER_MEMCACHE_ERROR_CODE  = 50001000;    //服务器常用错误码
    const SERVER_MEMCACHE_ERROR_STRING  = '服务器缓存错误';    //服务器常用错误码

    const SERVER_REDIS_ERROR_CODE  = 50001001;    //服务器常用错误码
    const SERVER_REDIS_ERROR_STRING  = '服务器缓存错误';    //服务器常用错误码

    const SERVER_MYSQL_ERROR_CODE  = 50001002;    //服务器常用错误码
    const SERVER_MYSQL_ERROR_STRING  = '服务器数据库错误';    //服务器常用错误码

    const SERVER_MONGO_ERROR_CODE  = 50001003;    //服务器常用错误码
    const SERVER_MONGO_ERROR_STRING  = '服务器MONGO错误';    //服务器常用错误码

    const SERVER_METAQ_ERROR_CODE  = 50001004;    //服务器常用错误码
    const SERVER_METAQ_ERROR_STRING  = '服务器METAQ错误';    //服务器常用错误码

    const SERVER_RKQ_ERROR_CODE  = 50001005;    //服务器常用错误码
    const SERVER_RKQ_ERROR_STRING  = '服务器RKQ错误';    //服务器常用错误码

    //文件错误代码
    const FILE_NOEXIST_ERROR_CODE  = 60001000;    //服务器常用错误码
    const FILE_NOEXIST_ERROR_STRING  = '文件不存在';    //服务器常用错误码

    const FILE_UPLOAD_ERROR_CODE  = 60001001;    //服务器常用错误码
    const FILE_UPLOAD_ERROR_STRING  = '上传文件出错';    //服务器常用错误码

    const FILE_OVERMAX_ERROR_CODE  = 60001002;    //服务器常用错误码
    const FILE_OVERMAX_ERROR_STRING  = '文件大小超出限制';    //服务器常用错误码

    //订单错误代码
    const ORDER_NOID_ERROR_CODE = 70001000;
    const ORDER_NOID_ERROR_STRING  = '订单不存在';

    const ORDER_NOREADY_ERROR_CODE = 70001006;
    const ORDER_NOREADY_ERROR_STRING  = '订单不是待确定状态';


    const ORDER_HASTEMPLATE_ERROR_CODE = 70001001;
    const ORDER_HASTEMPLATE_ERROR_STRING  = '流程已存在';

    const ORDER_NOTEMPLATE_ERROR_CODE = 70001002;
    const ORDER_NOTEMPLATE_ERROR_STRING  = '未找到流程模板';

    const ORDER_NOMATCH_ERROR_CODE = 70001003;
    const ORDER_NOMATCH_ERROR_STRING  = '流程不匹配';//流程不匹配

    const ORDER_NOSTART_ERROR_CODE = 70001004;
    const ORDER_NOSTART_ERROR_STRING  = '当前流程未开始';//流程未开始

    const ORDER_NOEND_ERROR_CODE = 70001005;
    const ORDER_NOEND_ERROR_STRING  = '前置流程未结束';//前置流程未结束

    const ORDER_HASOVER_ERROR_CODE = 70001006;
    const ORDER_HASOVER_ERROR_STRING  = '已经备货完毕';

    const ORDER_NOTRANS_ERROR_CODE = 70001007;
    const ORDER_NOTRANS_ERROR_STRING  = '没有任何备货';

    const ORDER_NOFLOW_ERROR_CODE = 70001008;
    const ORDER_NOFLOW_ERROR_STRING = '没有匹配订单流程';

    const ORDER_NOSTEP_ERROR_CODE = 70001009;
    const ORDER_NOSTEP_ERROR_STRING = '不是当前执行步骤';

    const ORDER_WAITREVIEW_ERROR_CODE = 70001010;
    const ORDER_WAITREVIEW_ERROR_STRING = '请等待对方再次提交复核申请';

    const ORDER_NODATA_ERROR_CODE = 70001011;
    const ORDER_NODATA_ERROR_STRING = '没有数据提交';

    const ORDER_PAYMENT_STATUS_ERROR_CODE = 70001012;
    const ORDER_PAYMENT_STATUS_ERROR_STRING = '您还有未确认收款的款项';


    const ORDER_UNFREEZE_ERROR_CODE = 80001000;

    const PAY_NOID_ERROR_CODE = 80001001;
    const PAY_NOID_ERROR_STRING  = '该单号不存在';

    const PAY_NOEXIST_ERROR_CODE = 80001002;
    const PAY_NOEXIST_ERROR_STRING  = '该单据不存在';

    const PAY_HASPAID_ERROR_CODE = 80001003;
    const PAY_HASPAID_ERROR_STRING  = '该单据已经支付';

    const PAY_NODETAIL_ERROR_CODE = 80001004;
    const PAY_NODETAIL_ERROR_STRING  = '缺少制单详情';

    const PAY_OVERLIMIT_ERROR_CODE = 80001005;
    const PAY_OVERLIMIT_ERROR_STRING  = '制单金额超过匹配订单剩余金额';

    const PAY_NOMATCH_ERROR_CODE = 80001006;
    const PAY_NOMATCH_ERROR_STRING  = '详情金额与制单金额不匹配';

    const PAY_BANK_ERROR_CODE = 80001007;
    const PAY_BANK_ERROR_STRING  = '银行接口返回错误';









}