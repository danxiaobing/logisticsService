<?php
/**
 * Created by PhpStorm.
 * User: amor
 * Date: 2017/8/3
 * Time: 13:31
 */

use Hprose\Client;
class Examine_UsersModel
{
    public $dbh = null;
    public $mc = null;
    public $Verify;

    /**
     * Constructor
     *
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mc = null)
    {
//        $rpc=Yaf_Registry::get("msg");
//        $this->Verify=Client::create( $rpc->host.'Verify',false);

        $this->dbh = $dbh;
    }


    /**
     * 用户列表
     * @param array $params
     * @return array $data
     */
    public function getUserList($params){
        $filter = array();
        $where = '';

        #检测参数
        if (isset($params['mobile']) && $params['mobile'] != '') {

            $filter[] = "gl_user_info.`mobile` LIKE '%{$params['mobile']}%'";
        }
        if (isset($params['email']) && $params['email'] != '') {
            $filter[] = " gl_user_info.`email`  LIKE '%{$params['email']}%'";
        }
        if (isset($params['user_name']) && $params['user_name'] != '') {
            $filter[] = " gl_user_info.`user_name` LIKE  '%{$params['user_name']}%'";
        }

        $where .= ' gl_companies.`is_del` = 0 ';
        #条件
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }

        #设置分页参数
        $page = isset($params['pageCurrent'])? intval($params['pageCurrent']) : 1;
        $pageSize = isset($params['pageSize'])? intval($params['pageSize']) : 9;

        #sql语句
        $sql = "SELECT gl_user_info.id,gl_user_info.is_del,gl_user_info.user_name,gl_user_info.mobile,gl_user_info.email,gl_companies.company_code,gl_companies.province_id,gl_companies.company_name,gl_companies.city_id,gl_companies.area_id,gl_companies.company_address,gl_companies.company_user,gl_companies.company_telephone,conf_area.area,conf_province.province,conf_city.city FROM gl_user_info
                LEFT JOIN gl_companies ON gl_companies.id = gl_user_info.cid
                LEFT JOIN conf_area ON conf_area.areaid = gl_companies.area_id
                LEFT JOIN conf_province ON conf_province.provinceid = gl_companies.province_id
                LEFT JOIN conf_city ON conf_city.cityid = gl_companies.city_id WHERE 
                {$where} 
                ORDER BY gl_user_info.`updated_at` DESC";

        $countSql = "SELECT COUNT(1) FROM gl_companies WHERE is_del = 0";

        $data['total'] = $this->dbh->select_one($countSql);
        $this->dbh->set_page_num($page);
        $this->dbh->set_page_rows($pageSize);

        $data['list'] =  $this->dbh->select($sql);

        return $data;
    }


    /**
     * 修改
     * @param integer  $id
     * @param array $params
     * @return boolean $data
     */
    public function updateUser($id,$params,$where ='')
    {
        $data = false;
        if(intval($id)){
            $data = $this->dbh->update('gl_user_info',$params,'id =' . intval($id));
        }elseif($where != ''){
            $data = $this->dbh->update('gl_user_info',$params,$where);
        }

        return $data;
    }


    /**
     *  获取个人信息
     * @param string $param
     * @return array
     */
    public function getUser($param,$password = ''){
        $where = 'gl_user_info.is_del != 1 AND';

        if(is_numeric($param) && strlen($param) >10){
            $where .= ' gl_user_info.`mobile` = '.$param;
        }elseif(preg_match('/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i',$param)){
            $where .= " gl_user_info.`email` = '{$param}'";
        }elseif(is_numeric($param)){
            $where .= " gl_user_info.`id` = '{$param}'";
        }



        $sql = "SELECT gl_user_info.user_name, gl_user_info.id,gl_user_info.password,gl_user_info.mobile,gl_user_info.email,gl_companies.company_code,gl_companies.id as cid,gl_companies.company_name,gl_companies.company_telephone,gl_companies.company_user,gl_companies.status  FROM gl_user_info
                LEFT JOIN gl_companies ON gl_companies.id = gl_user_info.cid WHERE 
                ".$where;

        $data =  $this->dbh->select_row($sql);

        if(!empty($password)) {
            $hash = $data['password'];
            if (password_verify($password, $hash)) {
                return $data;
            } else {
                return false;
            }
        }

        return $data;
    }



    public function register($params){

        $user = array(
            'email'      => $params['email'],
            'password'   => $params['password'],
            'mobile'     => $params['mobile'],
            'user_name'  => $params['user_name'],
            'is_del'     => 0,
            'created_at' => '=NOW()',
            'updated_at' => '=NOW()'
        );

        $carrier = array(
            'company_name'        => $params['company_name'],
            'company_user'        => $params['company_user'],
            'company_telephone'   => $params['company_telephone'],
            'province_id'         => $params['province_id'],
            'city_id'             => $params['city_id'],
            'area_id'             => $params['area_id'],
            'company_address'     => $params['company_address'],
            'status'              => 0,
            'is_del'              => 0,
            'created_at'          => '=NOW()',
            'updated_at'          => '=NOW()'
        );

        $carrier['company_code'] =   COMMON ::getCodeId('ZY56-');

        #开启事物
        $this->dbh->begin();
        try{
            #插入公司
           $carrier_id = $this->dbh->insert('gl_companies', $carrier);

           if(empty($carrier_id)){
               $this->dbh->rollback();
               return false;
           }

           #插入个人信息
            $user['cid']  = $carrier_id;
            $user_id = $this->dbh->insert('gl_user_info', $user);

            if(!empty($user_id)){
                $this->dbh->commit();
                return $user_id;
            }else{
                $this->dbh->rollback();
                return false;
            }

        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }

//    public function getMessage()
//    {
//        $data = $this->Verify->sendFunc('18627607669','你的手机号为18627607669');
//        if(!empty($data)){
//            return true;
//        }else{
//            return false;
//        }
//
//    }


    /**
     * 测试 接口连通
     */
    public  function  getCode($mobile,$code)
    {

        $sql = "SELECT * FROM sms WHERE `mobile`=".$mobile." AND `code` =".$code;
        return $this->dbh->select($sql);
    }

}
