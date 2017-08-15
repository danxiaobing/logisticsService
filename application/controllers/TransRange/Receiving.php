<?php
/**
 * @author  Jeff
 * @date    2017-8-14
 * @version $Id$
 */

class TransRange_ReceivingController extends Rpc {

    public function init() {
        parent::init();
    }


    public function getPageFunc($params)
    {
        $Dw = new TransRange_ReceivingModel(Yaf_Registry::get("db"));
        $data = $Dw->getPage($params);
        return $data;
    }

    public function getAllFunc($params)
    {
        $Dw = new TransRange_ReceivingModel(Yaf_Registry::get("db"));
        $data = $Dw->getAll($params);
        return $data;
    }

    public function getInfoFunc($id)
    {
        $Dw = new TransRange_ReceivingModel(Yaf_Registry::get("db"));
        $data = $Dw->getInfo($id);
        return $data;
    }

    public function addFunc($params)
    {
        $Dw = new TransRange_ReceivingModel(Yaf_Registry::get("db"));
        $data = $Dw->add($params);
        return $data;
    }

    public function updateFunc($params, $id)
    {
        $Dw = new TransRange_ReceivingModel(Yaf_Registry::get("db"));
        $data = $Dw->update($params, $id);
        return $data;
    }
    
    public function delFunc( $id)
    {
        $Dw = new TransRange_ReceivingModel(Yaf_Registry::get("db"));
        $data = $Dw->del($id);
        return $data;
    }
}

