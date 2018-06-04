<?php
/**
 * author Andy
 * date 2017-8-2
 */
class App_CategoryController extends Rpc {

    public function init() {
        parent::init();
        
    }

    //获取类目详情 daley
    public function getDetailFunc($id=0){
        $C = new App_CategoryModel(Yaf_Registry::get("gy_db"));
        return $C->getDetail($id);
    }



}
