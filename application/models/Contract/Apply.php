<?php

/**
 * Created by PhpStorm.
 * User: z
 * Time: 10:30
 */
class Contract_ApplyModel
{
    public $dbh = null;
    public $mc = null;

    /**
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null)
    {
        $this->dbh = $dbh;
        $this->mc = Yaf_Registry:: get("mc");
    }

    public function getContractApplyList($params)
    {
        //排序 别名
        if (isset($params['orders']) && $params['orders'] != '') {
            $ord = explode(",", $params['orders']);
            $ord_str = "tcca." . implode(",tcca.", $ord);
            $ord_str = str_replace("tcca.company_name", "gl_companies.company_name", $ord_str);
            $ord_str = str_replace("tcca.c_id", "gl_companies.id", $ord_str);
            $orders = "ORDER BY " . $ord_str;
        } else {
            $orders = "ORDER BY tcca.id DESC";
        }

        if ($params['start_created'] && $params['end_created']) {
            $filter[] = "tcca.`created_at` > '{$params['start_created']} 00:00:00' AND tcca.`created_at` < '{$params['end_created']} 23:59:59' ";
        } else if ($params['start_created']){
             
            $filter[] = "tcca.`created_at` > '{$params['start_created']} 00:00:00' ";
        }else if ($params['end_created']){
             
            $filter[] = "tcca.`created_at` < '{$params['end_created']} 23:59:59' ";
        }

        if ($params['start_deal'] && $params['end_deal']) {
            $filter[] = "tcca.`deal_time` > '{$params['start_deal']} 00:00:00' AND tcca.`deal_time` < '{$params['end_deal']} 23:59:59' ";
        } else if ($params['start_deal']){
             
            $filter[] = "tcca.`deal_time` > '{$params['start_deal']} 00:00:00' ";
        }else if ($params['end_deal']){
             
            $filter[] = "tcca.`deal_time` < '{$params['end_deal']} 23:59:59' ";
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = "tcca.`apply_status`={$params['status']}";
        }
        $where = '';
        if (count($filter) > 0) {
            $where .= ' WHERE ' . implode(' AND ', $filter);
        }

        $sql = "SELECT COUNT(*) FROM `gl_companies_contract_apply` AS tcca
                JOIN `gl_companies` ON gl_companies.`id`=tcca.`companies_id`
                {$where} ";

//        error_log($where, 3, 'sql_print.txt');
        $result['totalRow'] = $this->dbh->select_one($sql);
        if ($result['totalRow']) {
            if (isset($params['page']) && $params['page'] == false) {
                $sql = "SELECT gl_companies.id as c_id,gl_companies.company_name,
                        tcca.id,tcca.apply_status,tcca.created_at,tcca.updated_at,tcca.deal_time
                        FROM `gl_companies_contract_apply` AS tcca
                        JOIN `gl_companies` ON gl_companies.`id`=tcca.`companies_id`
                         {$where} {$orders}";
//                error_log($sql, 3, 'sql_print.txt');
                $result['list'] = $this->dbh->select($sql);
            } else {
                $result['totalPage'] = ceil($result['totalRow'] / $params['pageSize']);
                $this->dbh->set_page_num($params['pageCurrent']);
                $this->dbh->set_page_rows($params['pageSize']);
                $sql = "SELECT gl_companies.id as c_id,gl_companies.company_name,
                        tcca.id,tcca.apply_status,tcca.created_at,tcca.updated_at
                        FROM `gl_companies_contract_apply` AS tcca
                        JOIN `gl_companies` ON gl_companies.`id`=tcca.`companies_id`
                        {$where} {$orders}";
//                error_log($sql, 3, 'sql_print.txt');
                $result['list'] = $this->dbh->select_page($sql);
            }
        }
        return $result;
    }


     public function getContractApplyInfoById($id)
    {
        $sql = "SELECT gl_companies.id as c_id,gl_companies.*,
                tcca.id,tcca.apply_status,tcca.created_at,tcca.updated_at,tcca.deal_time
                FROM `gl_companies_contract_apply` AS tcca
                JOIN `gl_companies` ON gl_companies.`id`=tcca.`companies_id`
                WHERE tcca.`id`={$id}";
                // echo $sql;die;
        $result = $this->dbh->select_row($sql);
        return $result;
    }


    public function saveAccount($params, $id)
    {
        $result = $this->dbh->update('gl_companies_account', $params, 'id=' . $id);
        return $result;
    }


    /**
     * 新增企业开通资金账户信息申请
     * description
     * @Date   2017-03-07
     * @param  [array]     $data 
     * @return [int]                 
     */
    public function addContractApply($data){        
        return $this->dbh->insert('gl_companies_contract_apply',$data);
    }

    /**
     * 改变CA申请
     * description
     * @Date   2017-03-08
     * @param  [type]     $data   [description]
     * @param  [type]     $apply_id [description]
     * @return [type]               [description]
     */
    public function changeContractApply($data,$apply_id){ 
        if($apply_id > 0){
            $data['updated_at'] = '=NOW()';
            return $this->editContractApply($data,$apply_id);
        }else{
            $data['updated_at'] = '=NOW()';
            $data['created_at'] = '=NOW()';
            return $this->addContractApply($data);
        }       
    }

    /**
     * 编辑企业开通资金账户信息申请
     * description
     * @Date   2017-03-07
     * @param  [array]     $data 
     * @param  [int]     $id 
     * @return [int]                 
     */
    public function editContractApply($data,$id){        
        return $this->dbh->update('gl_companies_contract_apply',$data,'id=' . intval($id));
    }
    
}