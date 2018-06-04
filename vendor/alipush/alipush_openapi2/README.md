# Open API SDK for php developers

## Requirements

- PHP 5.3+

## Build

- to run unit tests, you will have to configure aliyun-sdk.properties files in your user directory, and make sure your project has corresponding service enabled, eg. openmr.

## Example

```php
use Push\Request\V20160801 as Push;
use Core\Profile\DefaultProfile;
use Core\DefaultAcsClient;
use App\Models\PushLog;
include app_path().'\..\vendor\alisdk\aliyun-php-sdk-core\Config.php';

$iClientProfile = \Core\Profile\DefaultProfile::getProfile("cn-hangzhou", $this->pushInfo['AccessKeyID'], $this->pushInfo['AccessKeySecret']);
$client = new DefaultAcsClient($iClientProfile);

$request = new Push\PushRequest();
$request->setTarget($target); //推送目标: DEVICE:推送给设备; ACCOUNT:推送给指定帐号,TAG:推送给自定义标签; ALL: 推送给全部
$request->setTargetValue($targetValue); //根据Target来设定，如Target=device, 则对应的值为 设备id1,设备id2. 多个值使用逗号分隔.(帐号与设备有一次最多100个的限制)
$request->setDeviceType($deviceType); //设备类型 ANDROID iOS ALL.
$request->setPushType($pushType); //消息类型 MESSAGE NOTICE
$request->setTitle($title); // 消息的标题
$request->setBody($body); // 消息的内容
$response = $client->getAcsResponse($request);
print_r($response);
```
## Authors && Contributors

- [Zuhe]()
- [Ma Lijie](https://github.com/malijiefoxmail)

## License

licensed under the [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0.html)
