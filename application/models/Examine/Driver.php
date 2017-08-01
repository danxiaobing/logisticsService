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
            $filter[] = " name like '%{$serach['name']}%' ";
        }
        if(isset($serach['mobile']) && $serach['mobile'] != ''){
            $filter[] = " mobile = '{$serach['mobile']}' ";
        }
        $WHERE = " WHERE 1 ";
       

        if(count($filter) > 0){
            $WHERE .= ' AND '.implode('AND', $filter);
        }

        $sql = " SELECT count(*) FROM gl_driver {$WHERE}";
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
            $sql = "SELECT * FROM gl_driver {$WHERE} ORDER BY updated_at DESC "; 
            $result['list'] = $this->dbh->select($sql);
        }

        return $result;

    }

    //更新状态
    public function updateStatus($status,$where){
       return $this->dbh->update('gl_driver',$status,$where); 
    }


}