<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class CityModel
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


    /**
     *  获取省的参数
     */
    public function getprovince()
    {
        $sql = "SELECT * FROM conf_province WHERE 1";
        return $this->dbh->select($sql);
    }

    /**
     * 获取市的参数
     */
    public function getcityById($id)
    {
        $sql = " SELECT * FROM conf_city WHERE father=".$id;
        $data = $this->dbh->select($sql);
        return $data;
    }
    /**
     * 获取市的参数
     */
    public function getcityByCityId($id)
    {
        $sql = " SELECT * FROM conf_city WHERE cityid=".$id;
        $data = $this->dbh->select($sql);
        return $data;
    }

    /**
     * 获取县的参数
     */
    public function getareaById($id)
    {
        $sql = "SELECT * FROM  conf_area WHERE father = ".$id;
        $data = $this->dbh->select($sql);
        return $data;
    }
    /**
     * 获取县的参数
     */
    public function getareaByAreaId($id)
    {
        $sql = "SELECT * FROM  conf_area WHERE areaid = ".$id;
        $data = $this->dbh->select($sql);
        return $data;
    }

    /**
     *  获取地区
     */
    public function getpalce($palce)
    {
        //获取省
        $sql = " SELECT provinceid,province FROM conf_province WHERE `province` like '%{$palce}%'";
        $province = $this->dbh->select($sql);
        //获取市
        $sql = " SELECT cityid,city,father cpid FROM conf_city WHERE `city` like '%{$palce}%'";
        $city = $this->dbh->select($sql);
        //县
        $sql = " SELECT areaid,area,father xpid FROM conf_area WHERE `area` like '%{$palce}%'";
        $area = $this->dbh->select($sql);
        //数据处理
        $arr = array();
        foreach ($province as $k=> $v) {
            $arr[] = $v['provinceid'];
        }
        foreach ($city as $k=> $v) {
            $arr[] = $v['cityid'];
        }
        foreach ($area as $k=> $v) {
            $arr[] = $v['areaid'];
        }
        return $arr;
    }

    //省市县数据获取
    public function getPlaceList($id){
        //获取省
        $sql = ' SELECT provinceid,province FROM conf_province ';
        $province = $this->dbh->select($sql);
        //获取市
        $sql = ' SELECT cityid,city,father cpid FROM conf_city ';
        $city = $this->dbh->select($sql);
        //县
        $sql = ' SELECT areaid,area,father xpid FROM conf_area';
        $area = $this->dbh->select($sql);
        //数据处理
        $arr = array();
       
        $ownPid = array();
        $ownCid = array();
        $ownAid = array();

        //方案一
        if($id){
            $sql = 'SELECT province_id,city_id,area_id FROM gl_companies_range_region WHERE r_id='.intval($id);
            $res = $this->dbh->select_row($sql);
            $ownPid = explode(',', $res['province_id']);
            $ownCid = explode(',', $res['city_id']);
            $ownAid = explode(',', $res['area_id']);
        }


        //省循环
         foreach ($province as $k => $v) {
             $children2 =array();
             //市循环
             foreach ($city as $k1 => $v1) {
                 if($v['provinceid'] == $v1['cpid']){
                     //县循环
                     $children3 = array();
                     foreach ($area as $k2 => $v2) {
                         if($v2['xpid'] == $v1['cityid']){
                             //保存县数据
                             if(in_array($v2['areaid'],$ownAid)){
                                $children3[] = array('id'=>$v2['areaid'],'name'=>$v2['area'],'checked'=>true);
                             }else{
                                $children3[] = array('id'=>$v2['areaid'],'name'=>$v2['area'],'checked'=>false);
                             }
                         }
                     }
                     //保存市数据
                     if(in_array($v1['cityid'],$ownCid)){
                        $children2[] =  array('id'=>$v1['cityid'],'name'=>$v1['city'],'isParent'=>true,'children'=>$children3,'checked'=>true);
                     }else{
                        $children2[] =  array('id'=>$v1['cityid'],'name'=>$v1['city'],'isParent'=>true,'children'=>$children3,'checked'=>false);
                     }
                     
                 }
             }
             //省数据
             if(in_array($v['provinceid'],$ownPid)){
                $arr[] =  array('id'=>$v['provinceid'],'name'=>$v['province'],'isParent'=>true,'children'=>$children2,'checked'=>true);
             }else{
                $arr[] =  array('id'=>$v['provinceid'],'name'=>$v['province'],'isParent'=>true,'children'=>$children2,'checked'=>falses);
             }
         }
         $data = json_encode($arr,JSON_UNESCAPED_UNICODE);
        return array('res'=>$data,'province'=>$province);
    }



    /**
     * 获取城市
     */
    public function getConfCity()
    {
        $sql = "SELECT * FROM conf_city ORDER BY zm";
        return $this->dbh->select($sql);
    }
}
