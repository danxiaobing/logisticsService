<?php
use OSS\Core\OssException;
use OSS\OssClient;
use Hprose\Client;

class IndexController extends Yaf_Controller_Abstract
{

    private $code = '0000';
    private $msg = 'success';
    private $data = array();

    /**
     * IndexController::init()
     *
     * @return void
     */
    public function init()
    {
        # parent::init();
    }

    /**
     * 显示整个后台页面框架及菜单
     *
     * @return string
     */
    public function IndexAction()
    {


	//$params['uid'] = '';
	//	$params['type'] ='';
	//	$params['categoryId'] = '';
	//	$params['regularMin'] = '';
		$params['regularMax'] = '';
		$params['priceMin'] = '';
		$params['priceMax'] = '';
		$params['deliverydate'] = '';
		$params['warehouse'] = '';
		$params['delivery'] = '';
		$params['curtype'] = 1;
		$params['productname'] = '';
        echo "service Index OK ";
//		$params=[];

			// $G = new GoodsModel(Yaf_Registry:: get("db"));
			// $this->data=$G->getSupplyDemand($params, $page, $row);

		//print_r($this->data);

//		if($this->code=='0000'){
//			$G = new GoodsModel(Yaf_Registry:: get("db"));
//			var_dump($G->getSupplyDemand($params, $page, $row));
//		}else{
//			echo 'fuck off';
//		}



//        $Web = new WebModel(Yaf_Registry::get("db"), Yaf_Registry:: get("mc"));
//        echo $Web->isEmail('asdasd.@sad.com');
//
//        $W = new WmsModel(Yaf_Registry::get("wms_db"));
//        $res = $W->getStorageList(array(), 10, 1);

//        $res = $this->updateGoodsByIdFunc('69293');
        //  $OC =     Client::create( 'order', false);
        // $OC = new OrderController($this->getRequest(),$this->getResponse(),$this->getView());
        //  $info = $OC->orderInfoFunc('37226');
//        $array = array(
//            'model' => 'storageapi',
//            'action' => 'getStorageStockList',
//            'company_id' => '5112',
//            'goodsno' => 'C00004',
//            'qualityno'=> '国标优等品',
//        );
//        $info = $this->cargoAndStockFunc( 'HYA0001',$array);
//        var_dump($info);
    }



}
