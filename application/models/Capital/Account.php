<?php

/**
 * [账户相关]
 * descriptio
 * @Date   2017-03-07
 * @version 1.0
 */

class Capital_AccountModel
{
    public $dbh = null;
    
    /**
     * @Date   2017-03-07
     * @param  [object]     $dbh 数据库句柄
     * @param  [object]     $mch 缓存句柄
     */
    public function __construct($dbh = null, $mch = null)
    {
        $this->dbh = Yaf_Registry::get("db");
    }

    /**
     * 查询企业开通资金账户信息
     * description
     * @Date   2017-03-07
     * @param  [int]     $company_id 
     * @return [array]                 
     */
    public function getCompanyAccount($company_id){        
        $sql = "SELECT bankname,bankaccountno,bankaccountname,date(created_at) as created_date FROM gl_companies_account WHERE companies_id='{$company_id}' limit 1";
        return $this->dbh->select_row($sql);  
    }

    /**
     * 查询企业开通资金账户信息申请
     * description
     * @Date   2017-03-07
     * @param  [int]     $company_id 
     * @return [array]                 
     */
    public function getCompanyApply($company_id){        
        $sql = "SELECT * FROM gl_companies_account_apply WHERE companies_id='{$company_id}' limit 1";
        return $this->dbh->select_row($sql); 
    }

    /**
     * 改变企业开通资金账户信息申请
     * description
     * @Date   2017-03-08
     * @param  [type]     $data   [description]
     * @param  [type]     $apply_id [description]
     * @return [type]               [description]
     */
    public function changeCompanyApply($data,$apply_id){ 
        if($apply_id > 0){
            $data['updated_at'] = '=NOW()';
            return $this->editCompanyApply($data,$apply_id);
        }else{
            $data['updated_at'] = '=NOW()';
            $data['created_at'] = '=NOW()';
            return $this->addCompanyApply($data);
        }       
    }

    /**
     * 新增企业开通资金账户信息申请
     * description
     * @Date   2017-03-07
     * @param  [array]     $data 
     * @return [int]                 
     */
    public function addCompanyApply($data){        
        return $this->dbh->insert('gl_companies_account_apply',$data);
    }

    /**
     * 新增提现申请
     * description
     * @Date   2017-03-09
     * @param  [type]     $data [description]
     */
    public function addWithdrawalsFunc($data){        
        return $this->dbh->insert('pay_takemoney',$data);
    }



    /**
     * 编辑企业开通资金账户信息申请
     * description
     * @Date   2017-03-07
     * @param  [array]     $data 
     * @param  [int]     $id 
     * @return [int]                 
     */
    public function editCompanyApply($data,$id){        
        return $this->dbh->update('gl_companies_account_apply',$data,'id=' . intval($id));
    }







}