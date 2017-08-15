<?php
/**
 *运力范围管理
 * @author  Andy
 * @date    2017-8-14
 * @version $Id$
 */

class TransRange_TransController extends Rpc {

    public function init() {
        parent::init();
    }

    //获取运力范围管理list
    public function getTransListFunc($search){
    	$T = new TransRange_TransModel(Yaf_Registry::get("db"));
        return $T->getTransList($search);
    }

    //新增运力范围管理
    public function addTransFunc($input,$data){
        $T = new TransRange_TransModel(Yaf_Registry::get("db"));
        return $T->addTrans($input,$data);
    }

    //获取信息 BY id
    public function getTransInfoFunc($id){
        $T = new TransRange_TransModel(Yaf_Registry::get("db"));
        return $T->getTransInfo($id);    	
    }

    //更新运力范围管理
    public function updateTransFunc($id,$input,$arr){
    	$T = new TransRange_TransModel(Yaf_Registry::get("db"));
        return $T->updateTrans($id,$input,$arr);  
    }


    //删除操作
    public function delTransFunc($id){
        $T = new TransRange_TransModel(Yaf_Registry::get("db"));
        return $T->delTrans($id);
    }
}

