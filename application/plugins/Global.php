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
    	     
    	     if($controller=='' || $action =='')
    	          die('403');
    	     
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
    	 
//         if ('view' === $request->getParam('out') || $request->isGet())
//         {
//             Yaf_Dispatcher::getInstance()->setView(new View(APPLICATION_PATH.'/application/views', array('root' => WEB_ROOT)));
//             Yaf_Registry::set('view', true);
//         }
//         else //isXmlHttpRequest, isPost, isPut, isDelete, isCli
         {
             Yaf_Dispatcher::getInstance()->disableView();
             Yaf_Registry::set('view', false);
         }
	}
  

	/**
	 * 操作处理结束之后执行
	 * @return void
	 */
/*
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {

	}
*/
}
