<?php
/**
 * Created by PhpStorm.
 * User: zhangbingxin
 * Date: 2018/6/22
 * Time: 15:27
 */
class App_Carrier_DispatchdetailModel
{
    public $dbh = null;

    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null)
    {
        $this->dbh = $dbh;
    }

    public function getCarrier($dispatch_id){
        $sql = "SELECT map.lng,map.lat,dis.created_at as time,goods.off_address,goods.reach_address,dis.driver_name as name,dis.dispatch_number FROM gl_order_dispatch as dis 
                LEFT JOIN gl_map_location as map ON dis.id=map.dispatch_id 
                LEFT JOIN gl_goods as goods ON dis.goods_id = goods.id
                WHERE dis.id=".$dispatch_id." 
                ORDER BY map.client_time DESC LIMIT 1
                ";
        $result = $this->dbh->select($sql);
        if ($result){
            return $result;
        }else{
            return false;
        }


    }



}
