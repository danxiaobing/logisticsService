<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/14
 * Time: 18:32
 */
class TransRange_TransModel
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
    public function getTransList($search){
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

        $where = 'where gcr.`is_del` = 0';
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
            $sql = "SELECT gcr.`id`,gcr.`areaname`,gcr.`user`,gcr.`mobile`,gcr.`is_black`,gcrr.`province_id` FROM gl_companies_range gcr LEFT JOIN  gl_companies_range_region gcrr ON gcrr.`r_id` = gcr.`id` {$where} order by gcr.`updated_at` DESC";
            $res = $this->dbh->select_page($sql);
            //获取省名
            foreach ($res as $k => $val) {
                $sql = "SELECT GROUP_CONCAT(cp.`province`) FROM conf_province cp where cp.`provinceid` in({$val['province_id']})";
                $res[$k]['province'] = $this->dbh->select_one($sql);
            }
            $result['list'] = $res;
            
        }
        return $result;
    }

    //新增运力范围管理
    public function addTrans($input,$data){
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


}
