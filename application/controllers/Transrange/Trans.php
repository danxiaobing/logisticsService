<?php
/**
 *运力范围管理
 * @author  Andy
 * @date    2017-8-14
 * @version $Id$
 */

class Transrange_TransController extends Rpc {

    public function init() {
        parent::init();
    }

    //获取运力范围管理list
    public function getTransListFunc($search,$id){
    	$T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->getTransList($search,$id);
    }

    //新增运力范围管理
    public function addTransFunc($input,$data,$black){
        $T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->addTrans($input,$data,$black);
    }

    //获取信息 BY id
    public function getTransInfoFunc($id){
        $T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->getTransInfo($id);    	
    }

    //更新运力范围管理
    public function updateTransFunc($id,$input,$arr,$black){
    	$T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->updateTrans($id,$input,$arr,$black);  
    }


    //删除操作
    public function delTransFunc($id){
        $T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->delTrans($id);
    }


    //获取黑白名单
    public function getBlackListFunc($id){
        $T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->getBlacklist($id);        
    }

    //获取匹配的承运商名单
    public function getTransMatchFunc($parameter){
        $T = new Transrange_TransModel(Yaf_Registry::get("db"));
        return $T->getTransMatch($parameter);   
    }
}

