<?php
//

//include '../vendor/alipush/alipush_openapi2/aliyun-php-sdk-core/Config.php';
include_once '../vendor/alipush/aliyun-php-sdk-core/Config.php';

//include_once '../library/aliyun-php-sdk-core/Config.php';
//
include '../vendor/alipush/aliyun-php-sdk-push/Push/Request/V20160801/PushRequest.php';

//
//
//
use \Push\Request\V20160801 as Push;

include '../vendor/alipush/aliyun-php-sdk-core/Profile/DefaultProfile.php';
include '../vendor/alipush/aliyun-php-sdk-core/DefaultAcsClient.php';

use \Profile\DefaultProfile;




//use Push\Request\V20160801 as Push;
//use Core\Profile\DefaultProfile;
//use Core\DefaultAcsClient;
//use App\Models\PushLog;
//include '../vendor/alipush/alipush_openapi2/aliyun-php-sdk-core/Config.php';
//
//echo "123";die;




class AccessController extends Yaf_Controller_Abstract
{

    /**
     * IndexController::init()
     *
     * @return void
     */
    public function init()
    {
        # parent::init();
    }

    /**
     * 显示整个后台页面框架及菜单
     *
     * @return string
     */
    public function IndexAction()
    {

        // 设置你自己的AccessKeyId/AccessSecret/AppKey
        $accessKeyId = "LTAIfLUszWRB952K";
        $accessKeySecret = "S0gAgPWKlVlrFBoOJBsys9lIa4STN5";
        $appKey = "24893288";



        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessKeySecret);
        $client = new DefaultAcsClient($iClientProfile);


        $request = new Push\PushRequest();

        $token = 'bade069a94e84e76b77cf5bd443f2a06,0d1090233a4848d885cb9be7e001a320';
        $user_id = '001,002';

        // 推送目标
        $request->setAppKey($appKey);
        $request->setTarget("ACCOUNT"); //推送目标: DEVICE:推送给设备; ACCOUNT:推送给指定帐号,TAG:推送给自定义标签; ALL: 推送给全部
        $request->setTargetValue($user_id); //根据Target来设定，如Target=device, 则对应的值为 设备id1,设备id2. 多个值使用逗号分隔.(帐号与设备有一次最多100个的限制)
        $request->setDeviceType("iOS"); //设备类型 ANDROID iOS ALL.
        $request->setPushType("NOTICE"); //消息类型 MESSAGE NOTICE
        $request->setTitle("我是通知001"); // 消息的标题
        $request->setBody("测试消息:我是文浩,智运消息"); // 消息的内容

        // 推送配置: iOS
        $request->setiOSBadge("5"); // iOS应用图标右上角角标
        $request->setiOSMusic("default"); // iOS通知声音
        $request->setiOSApnsEnv("DEV");//iOS的通知是通过APNs中心来发送的，需要填写对应的环境信息。"DEV" : 表示开发环境 "PRODUCT" : 表示生产环境
        $request->setiOSRemind("false"); // 推送时设备不在线（既与移动推送的服务端的长连接通道不通），则这条推送会做为通知，通过苹果的APNs通道送达一次(发送通知时,Summary为通知的内容,Message不起作用)。注意：离线消息转通知仅适用于生产环境
        $request->setiOSRemindBody("iOSRemindBody");//iOS消息转通知时使用的iOS通知内容，仅当iOSApnsEnv=PRODUCT && iOSRemind为true时有效
        $request->setiOSExtParameters("{\"k1\":\"ios\",\"k2\":\"v2\"}"); //自定义的kv结构,开发者扩展用 针对iOS设备

        // 推送控制
        $pushTime = gmdate('Y-m-d\TH:i:s\Z', strtotime('+3 second'));//延迟3秒发送
        $request->setPushTime($pushTime);
        $expireTime = gmdate('Y-m-d\TH:i:s\Z', strtotime('+1 day'));//设置失效时间为1天
        $request->setExpireTime($expireTime);
        $request->setStoreOffline("false"); // 离线消息是否保存,若保存, 在推送时候，用户即使不在线，下一次上线则会收到
        $response = $client->getAcsResponse($request);

        print_r("\r\n");
        print_r($response);



//
//        $iClientProfile = \Core\Profile\DefaultProfile::getProfile("cn-hangzhou", $this->pushInfo['AccessKeyID'], $this->pushInfo['AccessKeySecret']);
//        $client = new DefaultAcsClient($iClientProfile);
//
//        $request = new Push\PushRequest();
//        $request->setTarget($target); //推送目标: DEVICE:推送给设备; ACCOUNT:推送给指定帐号,TAG:推送给自定义标签; ALL: 推送给全部
//        $request->setTargetValue($targetValue); //根据Target来设定，如Target=device, 则对应的值为 设备id1,设备id2. 多个值使用逗号分隔.(帐号与设备有一次最多100个的限制)
//        $request->setDeviceType($deviceType); //设备类型 ANDROID iOS ALL.
//        $request->setPushType($pushType); //消息类型 MESSAGE NOTICE
//        $request->setTitle($title); // 消息的标题
//        $request->setBody($body); // 消息的内容
//        $response = $client->getAcsResponse($request);
//        print_r($response);


    }





}
