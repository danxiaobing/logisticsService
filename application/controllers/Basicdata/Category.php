<?php
/**
 * author Andy
 * date 2017-8-2
 */
class Basicdata_CategoryController extends Rpc {

    public function init() {
        parent::init();
        
    }

    //获取所有
    public function getAllFunc(){
      $C = new Basicdata_CategoryModel(Yaf_Registry::get("db"));
      return $C->getAll();
    }
    
    //列表信息
    public function getCateInfoFunc($serach){
    	$C = new Basicdata_CategoryModel(Yaf_Registry::get("db"));
    	return $C->getCateInfo($serach);
    }

    //新增类别
    public function addProductFunc($input){
      $C = new Basicdata_CategoryModel(Yaf_Registry::get("db"));
      return $C->addCate($input);      
    }


    
    //获取数据 by id
    public function getCateByIdFunc($id){
      $C = new Basicdata_CategoryModel(Yaf_Registry::get("db"));
      return $C->getInfoById($id);       
    }

    //更新数据
    public function updateCateFunc($id,$input){
      $C = new Basicdata_CategoryModel(Yaf_Registry::get("db"));
      return $C->updateCate($id,$input);   
    }

    //删除数据
    public function deleteCateFunc($id){
      $C = new Basicdata_CategoryModel(Yaf_Registry::get("db"));
      return $C->deleteCate($id);
    }


}
