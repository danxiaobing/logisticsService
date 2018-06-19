<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/16
 * Time: 14:15
 */
class Basicdata_FeedbackModel
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


    //列表详情
    public function getFeedback($serach)
    {
        $sql = " SELECT count(1) FROM gl_feedback ";
        //获取总的记录数
        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['list'] = array();
        if ($result['totalRow']) {
            //总的页数
            $result['totalPage'] = ceil($result['totalRow'] / $serach['pageSize']);
            //设置当前页 和 pagesize
            $this->dbh->set_page_num($serach['pageCurrent']);
            $this->dbh->set_page_rows($serach['pageSize']);
            //数据获取
            $sql = " SELECT gf.`id`,gf.`mobile`,gf.`content`,gd.`name` FROM gl_feedback gf LEFT JOIN gl_driver gd ON gd.`id` = gf.`driver_id`  ORDER BY gf.`id` DESC ";
            $result['list'] = $this->dbh->select_page($sql);
        }
        return $result;
    }

    public function getFile($id){
        $sql = " SELECT file FROM gl_feedback WHERE id= ".intval($id);
        return $this->dbh->select_one($sql);
    }
}