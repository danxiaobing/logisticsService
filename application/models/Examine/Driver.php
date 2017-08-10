<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class Examine_DriverModel
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

    //获取司机审核列表
    public function getDriverInfo($serach){

        $filter = array();
        if(isset($serach['name']) &&  $serach['name'] != ''){
            $filter[] = " gd.name like '%{$serach['name']}%' ";
        }
        if(isset($serach['mobile']) && $serach['mobile'] != ''){
            $filter[] = " gd.mobile  like '%{$serach['mobile']}%' ";
        }

        if(isset($serach['type']) && $serach['type'] != '-100'){
            $filter[] = " gd.type = {$serach['type']} ";
        }

        if(isset($serach['status']) && $serach['status'] != '-100'){
            $filter[] = " gd.status  ={$serach['status']} ";
        }

        if(isset($serach['companyid']) && $serach['companyid'] !='-100'){
            $filter[] = " gd.company_id = {$serach['companyid']} ";
        }

        $WHERE = " WHERE 1 ";
       
        if(count($filter) > 0){
            $WHERE .= ' AND '.implode('AND', $filter);
        }

        $sql = " SELECT count(1) FROM gl_driver gd {$WHERE}";
        //获取总的记录数
        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['list'] = array();
        if($result['totalRow']){
            //总的页数
            $result['totalPage']  = ceil($result['totalRow'] / $serach['pageSize']);  
            //设置当前页 和 pagesize
            $this ->dbh ->set_page_num($serach['pageCurrent']);
            $this ->dbh ->set_page_rows($serach['pageSize']); 
            //数据获取
            $sql = "SELECT id,name,mobile,sex,cid,type,driver_start,driver_end,practitioners,driver_status,driver_license,certificate_pic,other_pic,company_id,is_use FROM gl_driver gd {$WHERE} ORDER BY updated_at DESC ";
            $result['list'] = $this->dbh->select_page($sql);
        }

        return $result;

    }

    //更新状态
    public function updateStatus($status,$where){
       return $this->dbh->update('gl_driver',$status,$where); 
    }


    //证件查看
    public function getPic($id){
        $sql = " SELECT driver_license,certificate_pic,other_pic FROM gl_driver WHERE id =  ".intval($id);
        return $this->dbh->select_row($sql);
    }


    //隶属公司
    public function getCompany($companyid){
        $sql = 'SELECT gc.id,gc.company_name from gl_companies gc where (gc.id='.intval($companyid).' or gc.pid='.intval($companyid).')  and gc.status=2';
        return $this->dbh->select($sql);
    }

    //前台页面根据id获取数据
    public function getInfoById($id){
        $sql = " SELECT id,name,mobile,sex,cid,type,driver_start,driver_end,practitioners,driver_status,driver_license,certificate_pic,other_pic,company_id,is_use FROM gl_driver WHERE id= ".intval($id);
        return $this->dbh->select_row($sql);
    }

    //启用功能
    public function enabled($id,$param){
        return $this->dbh->update('gl_driver',$param,'id ='.intval($id));
    }


}