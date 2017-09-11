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
    public function getDriverInfo($serach,$id){

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
            if($serach['status'] == 0){
               $filter[] = " gd.status  ={$serach['status']} ";
            }elseif($serach['status'] == 1){
               $filter[] = " gd.is_use  ={$serach['status']} ";
            }else{
               $filter[] = " gd.is_use  =0";  
            }
            
        }

        if(isset($serach['companyid']) && $serach['companyid'] !='-100'){
            $filter[] = " gd.company_id = {$serach['companyid']} ";
        }

        if(isset($id) && $id != ''){
            //获取合作承运商公司id
            $sql = "SELECT GROUP_CONCAT(gc.id) FROM gl_companies  gc WHERE id = {$id} or pid= {$id}";
            $ids = $this->dbh->select_one($sql);
            $WHERE = " WHERE gd.isdelete = 0 AND gd.status <> 2 AND gd.company_id in ({$ids}) ";           
        }else{
            $WHERE = " WHERE gd.isdelete = 0 AND gd.status <> 2"; 
        }


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
            $sql = "SELECT id,name,mobile,sex,cid,type,driver_start,driver_end,practitioners,driver_status,company_id,is_use,status FROM gl_driver gd {$WHERE} ORDER BY updated_at DESC ";
            $result['list'] = $this->dbh->select_page($sql);
        }

        return $result;

    }
    public function getAllDriver($company_ids)
    {
        $ids = implode(',', $company_ids);
        $sql = "SELECT id,name,mobile FROM `gl_driver`  WHERE `status` = 1 AND `is_use` = 1 AND `isdelete` = 0 AND `type` in (1,3) AND `company_id` in ( {$ids} )";
        return $this->dbh->select($sql);
    }

    public function getAllEscort($company_ids)
    {
        $ids = implode(',', $company_ids);
        $sql = "SELECT id,name,mobile FROM `gl_driver`  WHERE `status` = 1 AND `is_use` = 1 AND `isdelete` = 0 AND `type` in (2,3) AND `company_id` in ( {$ids} )";
        return $this->dbh->select($sql);
    }

    //更新状态
    public function updateStatus($status,$where){
       return $this->dbh->update('gl_driver',$status,$where); 
    }


    //证件查看
    public function getPic($id,$type){
        // $sql = " SELECT driver_license,certificate_pic,other_pic FROM gl_driver WHERE id =  ".intval($id);
        $sql = "SELECT type,`path` FROM gl_driver_pic where cid=".intval($id)." AND type = {$type} ORDER BY updated_at DESC";
        if( $type == 3 ){
            return $this->dbh->select($sql);
        }else{
            return $this->dbh->select_row($sql);
        }
    }


    //隶属公司
    public function getCompany($companyid,$include_self = true){

        $where = ' gc.pid = '.intval($companyid) ;
        if($include_self){
            $where = '(gc.id='.intval($companyid).' or gc.pid='.intval($companyid).')';
        }

        $sql = 'SELECT gc.id,gc.company_name from gl_companies gc where '.$where.'  and gc.status=2';
        return $this->dbh->select($sql);
    }

    //前台页面根据id获取数据
    public function getInfoById($id){
        $sql = " SELECT id,name,mobile,sex,cid,type,driver_start,driver_end,practitioners,driver_status,driver_license,certificate_pic,other_pic,company_id,is_use,status FROM gl_driver WHERE id= ".intval($id);
        return $this->dbh->select_row($sql);
    }

    //删除信息
    public function delById($id){
        return $this->dbh->update('gl_driver',array('isdelete' => 1),'id='.intval($id));
    }

    //启用功能
    public function enabled($id,$param){
        return $this->dbh->update('gl_driver',$param,'id ='.intval($id));
    }


    //前台司机新增
    public function insertData($input){
        //基本信息
        $data = $input;
        unset($data['driver_license']);
        unset($data['certificate_pic']);
        unset($data['other_pic']);
        //事务
        $this->dbh->begin();
        try{
            //gl_driver 插入基本信息
            $id = $this->dbh->insert('gl_driver',$data);

            if(!$id){
                //回滚
               $this->dbh->rollback();
               return false;
            }

            //gl_driver_pic  插入图片信息
            $arr = array();
            if($input['driver_license'] != ''){
                $arr[] = array('cid'=>$id,'type' =>1,'path'=>$input['driver_license'],'created_at'=>'=NOW()','updated_at'=>'=NOW()');
            }
            if($input['certificate_pic'] != ''){
                $arr[] =  array('cid'=>$id,'type' =>2,'path'=>$input['certificate_pic'],'created_at'=>'=NOW()','updated_at'=>'=NOW()');
            }
            if(count($input['other_pic'])>0){
               foreach ($input['other_pic'] as $k => $val) {
                    if($val != ''){
                        $arr[] = array('cid'=>$id,'type' =>3,'path'=>$val,'created_at'=>'=NOW()','updated_at'=>'=NOW()');
                    }
                } 
            }

            $ids = array();
            foreach ($arr as $k => $val){
                $ids[] = $this->dbh->insert('gl_driver_pic',$val);
            }


            if(!empty($ids)){
                $this->dbh->commit();
                return $id;
            }else{
                $this->dbh->rollback();
                return false;
            }

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }

    //司机编辑
    public function updateData($input){
        //基本信息
        $data = $input;
        unset($data['driver_license']);
        unset($data['certificate_pic']);
        unset($data['other_pic']);
        unset($data['id']);
        //事务
        $this->dbh->begin();
        try{
            //gl_driver 更新基本信息
            $id = $this->dbh->update('gl_driver',$data,'id='.intval($input['id']));

            if(!$id){
                //回滚
               $this->dbh->rollback();
               return false;
            }

            //将先前图片delete=1
            $del = $this->dbh->update('gl_driver_pic',array('is_del' => 1),'cid='.intval($input['id']));
            if(!$del){
                $this->dbh->rollback();
                return false;
            }

            //gl_driver_pic  插入图片信息
            $arr = array();
            if($input['driver_license'] != ''){
                $arr[] = array('cid'=>$input['id'],'type' =>1,'path'=>$input['driver_license'],'created_at'=>'=NOW()','updated_at'=>'=NOW()');
            }
            if($input['certificate_pic'] != ''){
                $arr[] =  array('cid'=>$input['id'],'type' =>2,'path'=>$input['certificate_pic'],'created_at'=>'=NOW()','updated_at'=>'=NOW()');
            }
            if(count($input['other_pic'])>0){
               foreach ($input['other_pic'] as $k => $val) {
                    if($val != ''){
                        $arr[] = array('cid'=>$input['id'],'type' =>3,'path'=>$val,'created_at'=>'=NOW()','updated_at'=>'=NOW()');
                    }
                } 
            }

            //更新图片
            $ids = array();
            foreach ($arr as $k => $val){
                $ids[] = $this->dbh->insert('gl_driver_pic',$val);
            }


            if(!empty($ids)){
                $this->dbh->commit();
                return $id;
            }else{
                $this->dbh->rollback();
                return false;
            }

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }        
    }

    //司机图片
    public function getDiverPic($id){
        $sql = 'SELECT type,`path` FROM gl_driver_pic where cid='.intval($id).' AND is_del=0 ORDER BY updated_at DESC';
        return $this->dbh->select($sql);
    }



}