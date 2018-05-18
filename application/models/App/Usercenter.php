<?php
/**
 * Created by PhpStorm.
 * User: weicheng
 * Date: 2018/5/10
 * Time: 15:15
 */
class App_UsercenterModel
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


    public function add($params){
        $res = $this->dbh->insert('gl_feedback', $params);
        return $res;
    }

    //查看版本号码
    public function getVersions($type)
    {
        $sql = "SELECT * FROM td_mobile WHERE `status` = 1 AND `type` = '" . $type . "'  AND `app_type` = 3 ORDER BY id";
        //print_r($sql);die;
        return $this->dbh->select_row($sql);
    }


}