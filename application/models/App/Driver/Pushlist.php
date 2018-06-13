<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class App_Driver_PushlistModel
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


    public function getList($driverid,$page,$pagesize){
        $offset = ($page - 1)*$pagesize;
        $offset = $offset > 0 ? $offset : 0;
        $sql = 'SELECT count(id) as nums,company_id,title,content,dispatch_id,dispatch_number,type,status FROM gl_message WHERE driver_id='.intval($driverid).' AND is_del=0 Limit '.$offset.','.$pagesize;
        $res = $this->dbh->select($sql);
        return $res ? $res : [];
    }



}
