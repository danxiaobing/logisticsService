<?php
/**
 * Created by PhpStorm.
 * User: ai
 * Date: 2018/6/22
 * Time: 09:32
 */
class App_Carrier_MapModel
{
    public $dbh = null;

    /**
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null)
    {
        $this->dbh = $dbh;
    }
    /**
     * 保存位置信息
     * @param integer $data
     * @return array $data
     */
    public function addLocationInfogather($data){  
        $this->dbh->begin();
        try{
            $info = [
                'dispatch_id'=>addslashes($data['dispatch_id']),
                'lng'=>addslashes($data['lng']),
                'lat'=>addslashes($data['lat']),
                'created_at'=>'=NOW()',
                'client_time'=>$data['time']?addslashes($data['time']):'=NOW()',
            ];

            $map_location_info = $this->dbh->insert('gl_map_location', $info);
            if(!$map_location_info){
                throw new Exception('位置信息保存失败', 300);
                return false;
            }
            $this->dbh->commit();
            return true;
        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
            //return ['code' => $e->getCode(), 'msg'=>$e->getMessage()];
        }
    }

    /**
     * 获取轨迹
     * @param $dispatch_id int
     * @author amor
     */
    public function getLocus($dispatch_id){

        $where = 'god.is_del=0 ';

        if($dispatch_id && is_numeric($dispatch_id)){
            $where .= ' AND god.`id` = '.$dispatch_id;
        }else{
            return [];
        }
        $sql = "SELECT god.driver_name,god.driver_id,god.dispatch_number,gd.mobile,gml.lng,gml.lat,gml.created_at,gg.off_address,gg.reach_address
                FROM gl_order_dispatch god 
                LEFT JOIN gl_map_location gml ON god.id = gml.dispatch_id
                LEFT JOIN gl_driver gd ON gd.id = god.driver_id
                LEFT JOIN gl_goods gg ON gg.id = god.goods_id
                WHERE ".$where;  
        $sql .= ' order by gml.created_at desc';
        $data =  $this->dbh->select($sql);
        if(empty($data)) {
            return [];
        }
        return $data;

    }



}
