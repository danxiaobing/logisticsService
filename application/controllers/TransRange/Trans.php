<?php
/**
 * @author  Andy
 * @date    2017-8-14
 * @version $Id$
 */

class TransRange_TransController extends Rpc {

    public function init() {
        parent::init();
    }

    //新增运力范围管理
    public function addTransFunc($input,$data){
        $T = new TransRange_TransModel(Yaf_Registry::get("db"));
        return $T->addTrans($input,$data);
    }
}

