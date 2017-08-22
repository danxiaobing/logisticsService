<?php
/*
 *运力范围管理
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/14
 * Time: 18:32
 */
class Transrange_TransModel
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


    //获取运力管理范围list
    public function getTransList($search,$id){
        $filter = array();
        if(isset($search['areaname']) && $search['areaname'] != ''){
            $filter[] = " gcr.areaname like '%{$search['areaname']}%'";
        }

        if(isset($search['user']) && $search['user'] != ''){
           $filter[] = " gcr.user like '%{$search['user']}%' "; 
        }

        if(isset($search['mobile']) && $search['mobile'] != ''){
            $filter[] = " gcr.user ='{$search['mobile']}' "; 
        }

        //获取合作承运商公司id
        $sql = "SELECT GROUP_CONCAT(gc.id) FROM gl_companies  gc WHERE id = {$id} or pid= {$id}";
        $ids = $this->dbh->select_one($sql);

        $where = "where gcr.`is_del` = 0 AND gcr.cid in({$ids})";
        if(count($filter)>0){
            $where .= ' AND '.implode(' AND ',$filter);
        }


        $sql = "SELECT COUNT(1) FROM gl_companies_range gcr {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['list'] = array();
        if($result['totalRow']){
            //总的页数
            $result['totalPage']  = ceil($result['totalRow']/$search['pageSize']);  
            //设置当前页 和 pagesize
            $this->dbh->set_page_num($search['pageCurrent']);
            $this->dbh->set_page_rows($search['pageSize']);
            //数据获取
            $sql = "SELECT gcr.`id`,gcr.`areaname`,gcr.`user`,gcr.`mobile`,gcr.`is_black`,IFNULL(gcrr.`province_id`,0) province_id FROM gl_companies_range gcr LEFT JOIN  gl_companies_range_region gcrr ON gcrr.`r_id` = gcr.`id` {$where} order by gcr.`updated_at` DESC";
            $datas = $this->dbh->select_page($sql);
            $res = $datas ? $datas :[];
            //获取省名
            foreach ($res as $k => $val) {
                $sql = "SELECT GROUP_CONCAT(cp.`province`) FROM conf_province cp where cp.`provinceid` in({$val['province_id']})";
                $data = $this->dbh->select_one($sql);
                $res[$k]['province'] = $data ? $data:'';
            }
            $result['list'] = $res;
           
        }
        return $result;
    }

    //新增运力范围管理
    public function addTrans($input,$data,$black){
        //事务
        $this->dbh->begin();
        try{
            //gl_companies_range 插入基本信息
            $id = $this->dbh->insert('gl_companies_range',$input);
            if(!$id){
                //回滚
               $this->dbh->rollback();
               return false;
            }

            //gl_companies_range_black  黑名单信息
            $black['cid'] = $id;
            $black['updated_at'] = '=NOW()';
            $black['created_at'] = '=NOW()';
            $bl = $this->dbh->insert('gl_companies_range_black',$black);
            if(!$bl){
                $this->dbh->rollback();
                return false;                
            }
            //gl_companies_range_region  插入关联信息
            //方案一
            $data['r_id'] = $id;
            $res =  $this->dbh->insert('gl_companies_range_region',$data);

            if($res){
                $this->dbh->commit();
                return true;
            }else{
                $this->dbh->rollback();
                return false;
            }

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }


    //获取信息 by id
    public function getTransInfo($id){
        $sql = 'SELECT gcr.`areaname`,gcr.`user`,gcr.`mobile`,gcr.`is_black`  FROM gl_companies_range gcr where id='.intval($id);
        return $this->dbh->select_row($sql);
    }

    //更新运力范围
    public function updateTrans($id,$input,$arr,$black){
        //事务
        $this->dbh->begin();
        try{
            $res = $this->dbh->update('gl_companies_range',$input,'id='.intval($id));
            if(!$res){
               //回滚
               $this->dbh->rollback();
               return false;   
            }

            //更新黑白名单
            $bl = $this->dbh->update('gl_companies_range_black',$black,'cid='.intval($id));
            if(!$bl){
               //回滚
               $this->dbh->rollback();
               return false;   
            }


            $result = $this->dbh->update('gl_companies_range_region',$arr,'r_id='.intval($id));
            if($result){
                $this->dbh->commit();
                return true;
            }else{
                $this->dbh->rollback();
                return false;
            }

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }

    }

    //删除操作
    public function delTrans($id){
        return $this->dbh->update('gl_companies_range',array('is_del' => 1),'id='.intval($id));
    }

    //获取黑白名单
    public function getBlacklist($id){
        $sql = "SELECT blist  FROM gl_companies_range_black WHERE cid=".intval($id);
        return $this->dbh->select_one($sql);
    }


}
