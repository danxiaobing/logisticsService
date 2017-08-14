<?php
/**
 * author Andy
 * date 2017-8-2
 */
class Basicdata_ProductController extends Rpc {

    public function init() {
        parent::init();
        
    }
    //获取所有
    public function getByCateIdAll($id){
      $C = new Basicdata_ProductModel(Yaf_Registry::get("db"));
      return $C->getByCateIdAll($id);
    }

    //列表信息
    public function getProductFunc($serach){
    	$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->getProduct($serach);
    }

    //新增数据
    public function addProductFunc($input){
    	$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->addProduct($input);    	
    }

    //更新数据
    public function updateProductFunc($id,$input){
    	$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->updateProduct($id,$input);    	
    }

    //删除产品信息
    public function deleteProductFunc($params,$where){
    	$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->deleteProduct($params,$where);
    }

    //获取类别信息
   	public function getCateFunc(){
    	$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->getCate();   		
   	}

   	//获取数据By Id
   	public function getProductByCateIdFunc($id){
   		$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->getProductByCateId($id);
   	}

   	//查看MSDS证件
   	public function getImgSrcFunc($id){
   		$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->getImgSrc($id);
   	}

   	//删除图片
   	public function deleteImgFunc($id,$params){
   		$P = new Basicdata_ProductModel(Yaf_Registry::get("db"));
    	return $P->deleteImg($id,$params);
   	}





}
