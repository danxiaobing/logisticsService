<?php
/**
 * @author  Jeff
 * @date    2017-8-14
 * @version $Id$
 */

class Transrange_ReceivingController extends Rpc {

    public function init() {
        parent::init();
    }


    public function getPageFunc($params)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->getPage($params);
        return $data;
    }

    public function getAllFunc($params)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->getAll($params);
        return $data;
    }

    public function getInfoFunc($id)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->getInfo($id);
        return $data;
    }

    public function addFunc($params)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->add($params);
        return $data;
    }

    public function updateFunc($params, $id)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->update($params, $id);
        return $data;
    }
    public function updatePostFuncFunc($params, $id)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->updatePost($params, $id);
        return $data;
    }
    
    public function delFunc( $id)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->del($id);
        return $data;
    }

    public function getRualProducusFunc( $id)
    {
        $Dw = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        $data = $Dw->getRualProducus($id);
        return $data;
    }

    //获取黑白名单
    public function getFileWallFunc($rule_id){
        $T = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        return $T->getFileWall($rule_id);        
    }


    //智能接单
    public function matchingFunc($params){
        $T = new Transrange_ReceivingModel(Yaf_Registry::get("db"),Yaf_Registry::get("gy_db"));
        return $T->matching($params);  
    }
}

