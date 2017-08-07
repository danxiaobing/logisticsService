<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class Basicdata_CategoryModel
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
    public function getCateInfo($serach){
        $filter = array();
        if(isset($serach['name']) && $serach['name'] != ''){
            $filter[] = " gc.`name` like '%{$serach['name']}%' ";
        }
        if(isset($serach['covered']) && $serach['covered'] != ''){
            //根据涵盖产品名 查找所属的类别id
            // $join = ' LEFT JOIN gl_goods gd ON gc.id = gd.cateid ';
            $str = "SELECT GROUP_CONCAT(c.id SEPARATOR ',') from (SELECT gc.id id from gl_goods gd LEFT JOIN gl_category gc ON gd.cateid=gc.id  where gd.zh_name like '%{$serach['covered']}%' GROUP BY gc.id) c";
            $ids = $this->dbh->select_one($str);
            if(is_null($ids)){
                $ids = '0';
            }
            $filter[] = " gc.id in ({$ids})";
        }

        if(isset($serach['mark']) && $serach['mark'] != ''){
            $filter[] = " gc.`mark` like '%{$serach['mark']}%' ";
        }

        $WHERE = " WHERE gc.`is_del` = 0 ";
        if(count($filter)>0){
            $WHERE .= ' AND '.implode('AND', $filter);
        }

        $sql = " SELECT COUNT(*) FROM gl_category gc  {$WHERE} ";
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
            $sql = " SELECT gc.id,gc.name,substring_index(GROUP_CONCAT(gd.zh_name separator ','),',',3) as collect,count(gd.cateid) total,gc.mark FROM gl_goods gd LEFT JOIN gl_category  gc ON gd.cateid=gc.id {$WHERE} GROUP BY gd.cateid "; 
            $result['list'] = $this->dbh->select($sql);
        }
        return $result;
    }


    //新增类别
    public function addCate($input){
        return $this->dbh->insert('gl_category',$input);
    }

    //获取数据 by id
    public function getInfoById($id){
        $sql = " SELECT * FROM gl_category WHERE id=".intval($id);
        return $this->dbh->select_row($sql);
    }

    //更新数据
    public function updateCate($id,$input){
        return $this->dbh->update('gl_category',$input,'id='.intval($id));
    }

    //删除数据
    public function deleteCate($id){
        return $this->dbh->update('gl_category',array('is_del' => 1),'id='.intval($id));
    }
}
