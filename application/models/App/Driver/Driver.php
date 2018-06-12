<?php
/**
 * Created by PhpStorm.
 * User: weicheng
 * Date: 2018/5/10
 * Time: 15:15
 */
class App_Driver_DriverModel
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


    public function getDriver($mobile){
        $sql = " SELECT id,name,mobile,sex,cid,type,driver_start,driver_end,practitioners,driver_status,driver_license,certificate_pic,other_pic,company_id,is_use,status FROM `gl_driver` WHERE  `status` = 1 AND `is_use` = 1 AND `isdelete` = 0 AND `mobile`= ".$mobile;
        return $this->dbh->select_row($sql);
    }

}