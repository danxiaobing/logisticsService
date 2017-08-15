<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class Basicdata_ProductModel
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
    public function getProduct($serach){
        $filter = array();
        if(isset($serach['code']) && $serach['code'] != ''){
            $filter[] = " gd.`cas` like '%{$serach['code']}%' or gd.`un` like '%{$serach['code']}%' or gd.`cn` like '%{$serach['code']}%' ";
        }
        if(isset($serach['zh_name']) && $serach['zh_name'] != ''){
            $filter[] = " gd.`zh_name` like '%{$serach['zh_name']}%' ";
        }

        if(isset($serach['molecular']) && $serach['molecular'] != ''){
            $filter[] = " gd.`molecular` like '%{$serach['molecular']}%' ";
        }

        $WHERE = " WHERE gd.`delete` = 0 ";
        if(count($filter)>0){
            $WHERE .= ' AND '.implode('AND', $filter);
        }

        $sql = " SELECT count(*) FROM gl_products gd LEFT JOIN gl_category gc ON gd.`cateid` = gc.`id` {$WHERE} ";
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
            $sql = " SELECT gd.*,gc.`name` catename FROM gl_products gd LEFT JOIN gl_category gc ON gd.`cateid` = gc.`id` {$WHERE} ORDER BY gd.`updated_at` DESC "; 
            $result['list'] = $this->dbh->select_page($sql);
        }
        return $result;
    }

    //获取所有
    public function getProductByCateId($id){
        $sql = " SELECT id,zh_name,en_name FROM gl_products WHERE `delete` = 0 AND  `cateid` = ".intval($id);
        return $this->dbh->select($sql);
    }
     //获取所有品名
    public function getProductAll(){
        $sql = " SELECT id,zh_name,en_name FROM gl_products WHERE `delete` = 0 ";
        return $this->dbh->select($sql);
    }

    //新增数据
    public function addProduct($input){
        return $this->dbh->insert('gl_products',$input);
    }


    //更新数据
    public function updateProduct($id,$input){
        return $this->dbh->update('gl_products',$input,'id='.intval($id));
    }

    //删除产品信息
    public function  deleteProduct($params,$where){
        return $this->dbh->update('gl_products',$params,$where);
    }


    //获取类别信息
    public function getCate(){
        $sql = " SELECT id,name FROM gl_category ";
        return $this->dbh->select($sql);
    }

    //根据id查找
    public function getProductById($id){
        $sql = " SELECT * FROM gl_products WHERE id= ".intval($id);
        return $this->dbh->select_row($sql);
    }


    //查看MSDS证件
    public function getImgSrc($id){
        $sql = " SELECT msds FROM gl_products WHERE id= ".intval($id);
        return $this->dbh->select_one($sql);
    }

    //删除图片
    public function deleteImg($id,$params){
        return $this->dbh->update('gl_products',$params,'id='.intval($id));
    }


}