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
        $sql = " SELECT gld.id,gld.name,gld.mobile,gld.sex,gld.cid,gld.type,gld.driver_start,gld.driver_end,gld.practitioners,gld.driver_status,gld.driver_license,gld.certificate_pic,gld.other_pic,gld.company_id,gld.is_use,gld.status,glc.company_user,glc.company_telephone FROM `gl_driver` gld left join gl_companies glc on (gld.company_id=glc.id) WHERE  gld.`status` = 1 AND gld.`is_use` = 1 AND gld.`isdelete` = 0 AND gld.`mobile`= ".$mobile;
        return $this->dbh->select_row($sql);
    }

}