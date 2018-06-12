<?php
/**
 *
 * @author  James
 * @date    2011-10-25 15:00
 * @version $Id$
 */
class GlobalPlugin extends Yaf_Plugin_Abstract {

    /**
     * 操作正式处理之前执行，判断输出设定
     * @return void
     */
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        $controller = strtolower($request->getControllerName());
        if ($controller == 'wordpass') return;
        if ($controller == 'web') return;
        $params = ($request->getRequest('params', ''));


        //支持明文传输
        if ($params == '') {
            $req = $request->getRequest();
            // $req+= $request->getParams();
            $var = '';
            foreach ($req as $name => $val) {
                //echo $name.':'.$val."\n";
                $var.= $name . "=" . $val . "&";
            }
            $secret = new blowfish();
            $var = $secret->encrypt($var);
            $params = $var;
        }


        if ($params != '') {
            $secret = new blowfish();
            $string = $secret->decrypt($params);
            // $string = 'model=index&action=gettime';
            // $string = $secret->encrypt($string);
            // echo $string;
            parse_str($string, $data);
            $modules = $data['modules'];
            $controller = $data['model'];
            $action = $data['action'];
            if ($controller == '' || $action == '') {
                $arr = array(
                    'code' => '403'
                );
                die(json_encode($arr));
            }
            $controller = ucfirst($controller);
            $modules = ucfirst($modules);
            $request->setModuleName($modules);
            $request->setControllerName($controller);
            $request->setActionName($action);
            unset($data['modules']);
            unset($data['model']);
            unset($data['action']);
            $request->setParam($data);
        } else {
            $arr = array(
                'code' => '403'
            );
            die(json_encode($arr));
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

