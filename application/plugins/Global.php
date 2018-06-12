<?php
/**
 *
 * @author  James
 * @date    2011-10-25 15:00
 * @version $Id$
 */

class GlobalPlugin extends Yaf_Plugin_Abstract
{

	/**
	 * 操作正式处理之前执行，判断输出设定
	 * @return void
	 */
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {

    	$params = ($request->getRequest('params',''));

    	if($params !=''){

    	    $secret = new blowfish();

            $data = $secret->decrypt($params);

            $arr = explode('/',$data);
            $controller = $arr[1];
            $action = $arr[2];

            if($controller=='' || $action =='') die('403');

            $request->setControllerName($controller);
            $request->setActionName($action);


            $var = array();

    	    for($i = 3 ;$i < count($arr); $i++){
    	          $key = $arr[$i];
    	          $i++;
    	          $value = $arr[$i];

    	          $var[$key] = $value;
    	    }
    	    $request->setParam($var);
    	 } else {
    	     die('403');
    	 }

         {
             Yaf_Dispatcher::getInstance()->disableView();
             Yaf_Registry::set('view', false);
         }
	}
}
