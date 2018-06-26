<?php
/**
 * 供应商
 * Created by PhpStorm.
 * User: amor
 * Date: 2017/8/1
 * Time: 15:49
 */

class Examine_CarrierModel
{
    public $dbh = null;
    public $mc = null;

    /**
     * Constructor
     *
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mc = null)
    {
        $this->dbh = $dbh;
    }


    /**
     * 承运商列表
     * @param array $params
     * @return array $data
     */
    public function getCarrierList($params,$pid =''){

        $filter = array();
        $where = '';

        #检测参数
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filter[] = " gl_companies.`company_name`  LIKE '%{$params['company_name']}%' ";
        }
        if (isset($params['company_user']) && $params['company_user'] != '') {
            $filter[] = " gl_companies.`company_user`  LIKE '%{$params['company_user']}%' ";
        }
        if (isset($params['company_code']) && $params['company_code'] != '') {
            $filter[] = " gl_companies.`company_code`   LIKE '%{$params['company_code']}%' ";
        }

        if($pid == ''){
            $filter[] = " gl_companies.`pid` = 0";
        }else if( "cooperate" == $pid){
            $filter[] = " gl_companies.`pid` != 0";
        }else{
            $filter[] = " gl_companies.`pid` = {$pid}";
        }


        $where .= ' gl_companies.`status` != 0 ';
        #条件
        if (0 != count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }

        #sql语句
        $sql = "SELECT 
                  gl_companies.id,
                  gl_companies.company_code,
                  gl_companies.province_id,
                  gl_companies.company_name,
                  gl_companies.city_id,
                  gl_companies.area_id,
                  gl_companies.company_address,
                  gl_companies.company_user,
                  gl_companies.company_telephone,
                  gl_companies.social_code,
                  gl_companies.status,
                  conf_area.area,
                  conf_province.province,
                  conf_city.city,
                  gl_companies.business,
                  gl_companies.products 
                  FROM gl_companies 
                LEFT JOIN conf_area ON conf_area.areaid = gl_companies.area_id
                LEFT JOIN conf_province ON conf_province.provinceid = gl_companies.province_id
                LEFT JOIN conf_city ON conf_city.cityid = gl_companies.city_id WHERE 
                {$where} 
                ORDER BY gl_companies.`updated_at` DESC";

        $countSql = "SELECT COUNT(1) FROM gl_companies WHERE  {$where}";

        $data['totalRow'] = $this->dbh->select_one($countSql);
        $data['list'] = array();

        if($data['totalRow']){
            //总的页数
            $data['totalPage']  = ceil($data['totalRow'] / $params['pageSize']);
            $this->dbh->set_page_num($params['pageCurrent']);
            $this->dbh->set_page_rows($params['pageSize']);

            $data['list'] =  $this->dbh->select_page($sql);
        }

        return $data;
    }
    /**
     * 获取所有的一级承运商
     * @return array $data
     * @author daley
     * @date 2017/10/27
     */
    public function getOnelevelCarrierList(){

        $sql = "SELECT id,company_code,company_name FROM gl_companies  WHERE `status` = 2 and `is_del` = 0 and `pid` = 0 ORDER BY `id` asc";
        $data =  $this->dbh->select($sql);
        return $data ? $data:[];

    }

    /**
     * 承运商
     * @param int $params
     * @return array $data
     */
    public function getCarrier($id){
        $where = ' gl_companies.id = '.intval($id);
        $where .= ' AND gl_companies.`is_del` = 0 ';

        #sql语句
        $sql = "SELECT 
                  gl_companies.id,
                  gl_companies.company_code,
                  gl_companies.social_code,
                  gl_companies.province_id,
                  gl_companies.company_name,
                  gl_companies.city_id,
                  gl_companies.area_id,
                  gl_companies.company_address,
                  gl_companies.company_user,
                  gl_companies.company_telephone,
                  gl_companies.company_telephone,
                  gl_companies.company_mail,
                  gl_companies.status,
                  gl_companies.privilege_ca,
                  gl_companies.company_mail,
                  gl_companies.privilege_pay,
                  gl_companies.status,
                  gl_companies.qq,
                  conf_area.area,
                  conf_province.province,
                  conf_city.city,
                  gl_companies.business,
                  gl_companies.products  
                  FROM gl_companies 
                LEFT JOIN conf_area ON conf_area.areaid = gl_companies.area_id
                LEFT JOIN conf_province ON conf_province.provinceid = gl_companies.province_id
                LEFT JOIN conf_city ON conf_city.cityid = gl_companies.city_id WHERE  
               ".$where;


        $data =  $this->dbh->select_row($sql);
        if($data['privilege_ca'] == 1){
            $contractSql = "SELECT updated_at FROM gl_companies_contract_apply WHERE companies_id = ".intval($data['id']);
            $data['privilege_ca'] = $this->dbh->select_one($contractSql);
        }

        if($data['privilege_pay'] == 1){
            $accountSql = "SELECT auditstatus,updated_at,bankname FROM gl_companies_account_apply WHERE companies_id = ".intval($data['id']);
            $data['privilege_pay_data'] = $this->dbh->select_row($accountSql);
        }
        $res = $this->showfile($data['id']);
        if(!empty($res)){
            $data = array_merge($data, $res);
        }
        return $data;
    }

    /**
     * 修改承运商基础信息
     * @param array $params
     * @param integer $id
     * @return bool
     */
    public function updateCarrierBase($params,$id){

        $carrier_arr = array(
            'company_name'      =>$params['company_name'],
            'company_user'      =>$params['company_user'],
            'company_telephone' =>$params['company_telephone'],
            'social_code'        =>$params['social_code'],
            'seal_customer_id'     =>$params['seal_customer_id'],
            'privilege_pay'     =>$params['privilege_pay'],
            'privilege_ca'     =>$params['privilege_ca'],
            'privilege_sign'     =>$params['privilege_sign'],
            'company_mail'      =>$params['company_mail'],
            'status'            =>$params['status'],
            'updated_at'        => '=NOW()'
        );
        $carrier = $this->dbh->update('gl_companies', $carrier_arr,'id='.$id);
        return $carrier;
    }


    /**
     * 修改
     * @param array $params
     * @param integer $id
     * @return bool
     */
    public function updateCarrier($params,$id){
        $id = intval($id);
        $carrier_arr = array(
            'company_name'      =>$params['company_name'],
            'province_id'       =>$params['province_id'],
            'city_id'           =>$params['city_id'],
            'area_id'           =>$params['area_id'],
            'company_address'   =>$params['company_address'],
            'company_user'      =>$params['company_user'],
            'company_telephone' =>$params['company_telephone'],
            'status'            =>$params['status'],
            'social_code'        =>$params['social_code'],
            'business'          =>$params['business'],
            'privilege_pay'     =>$params['privilege_pay'],
            'privilege_ca'     =>$params['privilege_ca'],
            'privilege_sign'     =>$params['privilege_sign'],
            'created_at'        => '=NOW()',
            'updated_at'        => '=NOW()',
            'company_mail'      =>$params['company_mail'],
            'products'          => $params['products'],
            'qq'                => $params['qq']

        );
        #开启事物
        $this->dbh->begin();
        try{
        #修改公司
            $carrier = $this->dbh->update('gl_companies', $carrier_arr,'id='.$id);
            if(empty($carrier)){
                $this->dbh->rollback();
                return false;
            }

            $res = $this->dbh->update('gl_companies_pic',['is_del'=>1],'cid='.$id);
            if(empty($res)){
                $this->dbh->rollback();
                return false;
            }

            $pic = array(
                0=>['path'=>$params['danger_file'],'type'=>2,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'],
                1=>['path'=>$params['business_license'],'type'=>1,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'],
                2=>['path'=>$params['corporation_card'],'type'=>4,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'],
                3=>['path'=>$params['ca_warrant'],'type'=>5,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'],
                4=>['path'=>$params['ca_application'],'type'=>6,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'],
                5=>['path'=>$params['admin_warrant'],'type'=>7,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()']

            ) ;

            $n = 6;
            if(1 <= count($params['other_file'])){
                foreach ($params['other_file'] as $v){
                    $pic[$n] = ['path'=>$v,'type'=>3,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'];
                    $n++;
                }
            }

            foreach ($pic as $v){
                $data = '';
                $data = $this->dbh->insert('gl_companies_pic',$v);
                if(empty($data)){
                    $this->dbh->rollback();
                    return false;
                }
            }



            $this->dbh->commit();
            return true;

        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }



    }

    /**
     * 审核
     * @param array $params
     * @return array $data
     */
    public function examineCarrier($status,$where){

        //审核通过后
        if($status['status'] = 2){
            #开启事物
            $this->dbh->begin();
            try{
                #更新公司审核状态
                $companies_res =  $this->dbh->update('gl_companies', $status, $where );

                if(empty($companies_res)) {
                    $this->dbh->rollback();
                    return false;
                }

                $sql = "SELECT * FROM gl_companies  WHERE ".$where;
                $companiesdata =  $this->dbh->select_row($sql);

                //如果申请开通支付功能
                if($companiesdata['privilege_pay']==1){

                    $bankApply = [
                        'companies_id' => $companiesdata['id'],
                        'companyname' => $companiesdata['company_name'],
                        'legalpersonname' => '',
                        'certtype' => $companiesdata['type'],
                        'certno'   => $companiesdata['social_code'],
                        'commaddress' => $companiesdata['company_address'],
                        'contactname' => $companiesdata['company_user'],
                        'contactphone' => $companiesdata['company_telephone'],
                        'mailaddress'  => $companiesdata['company_mail'],
                        'auditstatus' => 1,
                        'created_at' => '=NOW()',
                        'updated_at' => '=NOW()'
                    ];

                    //插入资金账户申请
                    $account_apply_res = $this->dbh->insert('gl_companies_account_apply',$bankApply);
                    if(empty($account_apply_res)) {
                        $this->dbh->rollback();
                        return false;
                    }
                }


                $caApply = [
                    'companies_id' => $companiesdata['id'],
                    'apply_status' =>  1,
                    'created_at' => '=NOW()',
                    'updated_at' => '=NOW()'
                ];
                $contract_apply_res = $this->dbh->insert('gl_companies_contract_apply',$caApply);
                if(empty($contract_apply_res)) {
                    $this->dbh->rollback();
                    return false;
                }
                $this->dbh->commit();
                return true;

            } catch (Exception $e) {
                $this->dbh->rollback();
                return false;
            }

        }else{
            return $this->dbh->update('gl_companies', $status, $where );
        }


    }


    public function showfile($id){
        $sql = "SELECT  gl_companies_pic.`type`,gl_companies_pic.`path` FROM gl_companies_pic WHERE is_del = 0  AND cid = ".intval($id);

        $res = $this->dbh->select($sql);
        $data['danger_file'] = '';
        $data['other_file'] = '';
        $data['business_license'] = '';
        if($res){
            foreach ($res as $key=>$value){
                if( 1 == intval($value['type'])){
                    $data['business_license'] = $value['path'];
                }else if( 2 == intval($value['type'])) {
                    $data['danger_file'] = $value['path'];
                }else if(4  == intval($value['type'])){
                    $data['corporation_card'] = $value['path'];
                }else if(5  == intval($value['type'])){
                    $data['ca_warrant'] = $value['path'];
                }else if(6  == intval($value['type'])){
                    $data['ca_application'] = $value['path'];
                }else if(7  == intval($value['type'])){
                    $data['admin_warrant'] = $value['path'];
                }else{
                    $data['other_file'][$key] = $value['path'];
                }
            }
        }else{
            $data = array();
        }

        return $data;
    }

    public function delFile($status,$where){
        return $this->dbh->update('gl_companies_pic', $status, $where );
    }



    /**
     * 合作承运商列表
     * @param array $params
     * @return array $data
     */
    public function cooperateCarrier($params){

        $filter = array();

        #检测参数
        if (isset($params['company_name']) && $params['company_name'] != '') {
            $filter[] = " gl_companies.`company_name`  LIKE '%{$params['company_name']}%' ";
        }
        if (isset($params['company_user']) && $params['company_user'] != '') {
            $filter[] = " gl_companies.`company_user`  LIKE '%{$params['company_user']}%' ";
        }

        if (isset($params['status']) && $params['status'] != '') {
            if($params['status'] == 1){
              $filter[] = " gl_companies.`status` = 1 ";
            }elseif($params['status'] == 2){
              $filter[] = " gl_companies.`is_status` = 0 ";
            }elseif ($params['status'] == 3) {
              $filter[] = " gl_companies.`is_status` = 1 ";
            }
        }

        $filter[] = " gl_companies.`pid` = ".intval($params['pid']);


        $where = ' gl_companies.`status` != 0 AND gl_companies.`is_del` = 0';
        #条件
        if (0 != count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }

        #设置分页参数
        $page = isset($params['pageCurrent'])? intval($params['pageCurrent']) : 1;
        $pageSize = isset($params['pageSize'])? intval($params['pageSize']) : 9;


        #sql语句
        $sql = "SELECT 
                    gl_companies.id,
                    gl_companies.is_status,
                    gl_companies.company_code,
                    gl_companies.province_id,
                    gl_companies.company_name,
                    gl_companies.city_id,
                    gl_companies.area_id,
                    gl_companies.company_address,
                    gl_companies.company_user,
                    gl_companies.company_telephone,
                    gl_companies.`status`,
                    conf_area.area,
                    conf_province.province,
                    conf_city.city,
                    (SELECT count(1) FROM gl_fleets  WHERE  gl_fleets.`company_id` = gl_companies.`id`
	 )  AS fleets 
	            FROM gl_companies 
                LEFT JOIN conf_area ON conf_area.areaid = gl_companies.area_id
                LEFT JOIN conf_province ON conf_province.provinceid = gl_companies.province_id
                LEFT JOIN conf_city ON conf_city.cityid = gl_companies.city_id WHERE 
                {$where} 
                ORDER BY gl_companies.`updated_at` DESC";


        $countSql = "SELECT COUNT(1) FROM gl_companies WHERE is_del = 0 AND status != 0 AND pid = {$params['pid']}";

        $data['totalRow'] = $this->dbh->select_one($countSql);
        $data['totalpage']  = ceil($data['totalRow'] / $pageSize);
        $this->dbh->set_page_num($page);
        $this->dbh->set_page_rows($pageSize);

        $data['list'] =  $this->dbh->select($sql);

        return $data;
    }


    public function addCooperate($params){
        $id = $params['id'];
        $cooperate_arr = array(
            'company_name'      =>$params['company_name'],
            'province_id'       =>$params['province_id'],
            'city_id'           =>$params['city_id'],
            'area_id'           =>$params['area_id'],
            'company_address'   =>$params['company_address'],
            'company_user'      =>$params['company_user'],
            'company_telephone' =>$params['company_telephone'],
            'status'            =>$params['status'],
            'code'              =>$params['code'],
            'pid'               =>$params['pid'],
            'created_at' => '=NOW()',
            'updated_at' => '=NOW()',
        );



        if(!isset($cooperate_arr['code']) && !isset($params['danger_file']) && !isset($params['business_license'])){
            return false;
        }


        #开启事物
        $this->dbh->begin();
        try{

            if(!$id){

                #添加
                $cooperate_arr['company_code'] =   COMMON ::getCodeId('ZY56-');
                $cooperate = $this->dbh->insert('gl_companies', $cooperate_arr);
                $id = $cooperate;
                if(empty($cooperate)){
                    $this->dbh->rollback();
                    return false;
                }
            }else {

                #修改公司
                $cooperate = $this->dbh->update('gl_companies', $cooperate_arr,' id = '.intval($id));
                if(empty($cooperate)) {
                    $this->dbh->rollback();
                    return false;
                }
            }

            $res = $this->dbh->update('gl_companies_pic',['is_del'=>1],'cid='.$id);
            if(empty($res)){
                $this->dbh->rollback();
                return false;
            }

            $pic = array(
                0=>['path'=>$params['danger_file'],'type'=>2,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'],
                1=>['path'=>$params['business_license'],'type'=>1,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()']
            ) ;

            $params['other_file'] = array_filter($params['other_file']);
            $n = 2;
            if(1 <= count($params['other_file'])){
                foreach ($params['other_file'] as $v){
                    $pic[$n] = ['path'=>$v,'type'=>3,'cid'=>$id,'created_at'=>'=NOW()','updated_at'=>'=NOW()'];
                    $n++;
                }
            }

            $data = '';
            foreach ($pic as $v){
                $data = $this->dbh->insert('gl_companies_pic',$v);
                if(empty($data)){
                    $this->dbh->rollback();
                    return false;
                }
            }

            $this->dbh->commit();
            return true;

        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }

    /**
     * 更新合作承运商
     * @param $where
     * @param $id
     * @return mixed
     */
    public function updateCooperate($status,$id){
        return $this->dbh->update('gl_companies',$status,' id = '.intval($id));
    }
}