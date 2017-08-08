<?php
/**
 * @author  Andy
 * @date    2017-8-8
 * @version $Id$
 */

class CityController extends Rpc {

    public function init() {
        parent::init();
        
    }

    //省市县
    public function getCityFunc(){
      $C = new CityModel(Yaf_Registry::get("db"));
      return $C->getCity();
    }
}
