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

    //获取所有
    public function getAll(){
        $sql = " SELECT id,name FROM gl_category WHERE `is_del` = 0 ";
        return $this->dbh->select($sql);
    }

    //列表详情
    public function getCateInfo($serach){
        $filter = array();
        if(isset($serach['name']) && $serach['name'] != ''){
            $filter[] = " gc.`name` like '%{$serach['name']}%' ";
        }
        if(isset($serach['covered']) && $serach['covered'] != ''){
            //根据涵盖产品名 查找所属的类别id
            // $join = ' LEFT JOIN gl_products gd ON gc.id = gd.cateid ';
            $str = "SELECT GROUP_CONCAT(c.id SEPARATOR ',') from (SELECT gc.id id from gl_products gd LEFT JOIN gl_category gc ON gd.cateid=gc.id  where gd.zh_name like '%{$serach['covered']}%' GROUP BY gc.id) c";
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
            $sql = " SELECT gc.id,gc.name,substring_index(GROUP_CONCAT(gd.zh_name separator ','),',',3) as collect,count(gd.cateid) total,gc.mark FROM gl_category  gc  LEFT JOIN gl_products gd ON gd.cateid=gc.id {$WHERE} GROUP BY gc.id "; 
            $result['list'] = $this->dbh->select_page($sql);
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

    //获取商品类目接口
    public function getGrade($id,$grade){
        $res = [];

        if($grade == 1 && $id == 0){
            //一级类目
            $sql = "SELECT cat.`id`,cat.`title` FROM td_category_goods cat WHERE cat.`pid` = 0 AND cat.`grade`= 1 AND cat.`delete` = 0 AND cat.`showtype` <> 3 ";
            $res = $this->dbh->select($sql);
        }elseif($id != 0 && $grade == 2){
            //二级类目
            $sql = "SELECT cat.`id`,cat.`title` FROM td_category_goods cat WHERE cat.`pid` = ".intval($id)." AND cat.`grade`= 2 AND cat.`delete` = 0 AND cat.`showtype` <> 3 ";
            $res = $this->dbh->select($sql);
        }elseif($id != 0 && $grade == 3){
            //三级类目
            $sql = "SELECT cat.`id`,cat.`title` FROM td_category_goods cat WHERE cat.`pid` = ".intval($id)." AND cat.`grade`= 3 AND cat.`delete` = 0 AND cat.`showtype` = 2 ";
            $res = $this->dbh->select($sql);
        }
     
        return $res ? $res : [];
    }

    /**
     * 获取商品类目详情
     * @param $id
     * @return array
     * @author daley
     */
    public function getDetail($id){
        $sql = "SELECT
                    cat1.`id` ,
                    cat1.`pid` ,
                    cat1.`title` ,
                    cat1.`grade` ,
                    cat1.`showtype` ,
                    cat1.`is_recommend` ,
                    cat1.`order` ,
                    cat1.`keywords` ,
                    cat1.`english_name` ,
                    cat1.`description` 
                FROM
                    td_category_goods cat1
                WHERE
                    cat1.`id` =".intval($id);
        $res = $this->dbh->select_row($sql);
        return $res ? $res : [];

    }

    /**
     * 根据三级分类名称获取对应一级，二级分类
     */

    public function getCateByName($name){


        //获取三级分类
        $sql = "SELECT cat.`id`,cat.`title`,cat.`pid`,cat.`grade` FROM td_category_goods cat WHERE cat.`title` = '".trim($name)."' AND cat.`grade`= 3 AND cat.`delete` = 0 AND cat.`showtype` = 2";

        $cateGrade3 = $this->dbh->select_row($sql);
        if(!empty($cateGrade3)){
            //二级类目
            $sql = "SELECT cat.`id`,cat.`title`,cat.`pid`,cat.`grade` FROM td_category_goods cat WHERE cat.`id` = ".intval($cateGrade3['pid'])." AND cat.`grade`= 2 AND cat.`showtype` <> 3 ";

            $cateGrade2 = $this->dbh->select_row($sql);
            if(!empty($cateGrade2)){
                //一级类目
                $sql = "SELECT cat.`id`,cat.`title`,cat.`pid`,cat.`grade` FROM td_category_goods cat WHERE cat.`id` = ".intval($cateGrade2['pid'])." AND cat.`grade`= 1 AND cat.`showtype` <> 3 ";

                $cateGrade1 = $this->dbh->select_row($sql);
            }

        }else{
            $cateGrade3 = null;
        }
        $data = [
            'cateGradeOne'=>$cateGrade1,
            'cateGradeTwo'=>$cateGrade2,
            'cateGradeThree'=>$cateGrade3
        ];
        return $data;
    }

    /**
     * 获取所有的三级分类，同时返回所属的一级，二级分类
     */
    public function getCategoryProdut(){

        $sql = "SELECT
                        cat.`id`,
                        cat.`title`,
                        cat.`pid`,
                        cat.`grade`,
                        cat.`showtype`,
                        cattwo.`id` as cattwo_id,
                        cattwo.`pid` as cattwo_pid,
                        cattwo.`showtype` as cattwo_showtype,
                        catone.`id` as catone_id,
                        catone.`pid` as catone_pid,
                        catone.`showtype` as catone_showtype
                         FROM td_category_goods cat
                         LEFT JOIN td_category_goods cattwo ON  cattwo.id=cat.pid
                         LEFT JOIN td_category_goods catone ON  catone.id=cattwo.pid
                         WHERE  cat.`grade`= 3 AND cat.`delete` = 0 AND cat.`showtype` = 2  AND cattwo.`grade`= 2 AND cattwo.`showtype` <> 3  AND catone.`grade`= 1 AND catone.`showtype` <> 3";

        $catlist = $this->dbh->select($sql);

        return $catlist?$catlist:[];

    }


}
