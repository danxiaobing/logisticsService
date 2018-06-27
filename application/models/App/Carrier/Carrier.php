<?php
/**
 * Created by PhpStorm.
 * User: amor
 * Date: 2018/6/16
 * Time: 09:32
 */
class App_Carrier_CarrierModel
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
     * APP登陆
     * @param $username string
     * @param $userpwd string
     * @return array or boolen
     * @author amor
     */
    public function getCarrier($username,$userpwd){
        $where = 'gl_user_info.is_del != 1 AND';

        if(is_numeric($username) && strlen($username) >10){
            $where .= ' gl_user_info.`mobile` = '.$username;
        }elseif(preg_match('/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i',$username)){
            $where .= " gl_user_info.`email` = '{$username}'";
        }


        $sql = "SELECT 
                gl_user_info.user_name,
                gl_user_info.id,
                gl_user_info.password,
                gl_user_info.mobile,
                gl_user_info.email,
                gl_companies.company_code,
                gl_companies.id as cid,
                gl_companies.company_name,
                gl_companies.company_telephone,
                gl_companies.company_user,
                gl_companies.status  
                FROM gl_user_info
                LEFT JOIN gl_companies ON gl_companies.id = gl_user_info.cid WHERE 
                ".$where;

        $data =  $this->dbh->select_row($sql);
        if(!empty($userpwd)) {
            $hash = $data['password'];
            if (password_verify($userpwd, $hash)) {
                unset($data['password']);
                return $data;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }



}
