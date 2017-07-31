<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

if (phpversion() >= '5.3') {
	define('APPLICATION_PATH', __DIR__ . '/../');
} else {
	define('APPLICATION_PATH', dirname(__FILE__). '/../');
}

define('SSN_PASS',  'online');
define('SSN_INFO',  'msr');
define('SSN_LOG',   'log');
define('SSN_SA',    99999);

define('VAL_YES',     1);
define('VAL_NO',      0);
define('VAL_ALL',   100);
define('VAL_NONE', -100);

require_once '../vendor/autoload.php';


date_default_timezone_set('Asia/Shanghai');
$app = new Yaf_Application(APPLICATION_PATH .'/application/conf/app.ini');
$app->bootstrap()->run();

?>