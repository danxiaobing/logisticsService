<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class App_Carrier_CategoryModel
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



}
