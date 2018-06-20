<?php

/**
 * Created by PhpStorm.
 * User: xingjun
 * Date: 2017/3/21
 * Time: 10:30
 */
class Capital_CiticauditModel
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

    public function getList($params)
    {
        //排序 别名
        if (isset($params['orders']) && $params['orders'] != '') {
            $ord = explode(",", $params['orders']);
            $ord_str = "tcaa." . implode(",tcaa.", $ord);
            $ord_str = str_replace("tcaa.company_name", "gl_companies.company_name", $ord_str);
            $ord_str = str_replace("tcaa.c_id", "gl_companies.id", $ord_str);
            $orders = "ORDER BY " . $ord_str;
        } else {
            $orders = "ORDER BY tcaa.id DESC";
        }

        if (isset($params['start_created']) && $params['start_created'] != '' && isset($params['end_created']) && $params['end_created'] != '') {
            if ($params['start_created'] == $params['end_created']) {
                $filter[] = "tcaa.`created_at` LIKE '%{$params['start_created']}%'";
            } else {
                $filter[] = "tcaa.`created_at` > '{$params['start_created']}' AND tcaa.`created_at` < '{$params['end_created']} 23:59:59'";
            }
        }

        if (isset($params['start_updated']) && $params['start_updated'] != '' && isset($params['end_updated']) && $params['end_updated'] != '') {
            if ($params['start_updated'] == $params['end_updated']) {
                $filter[] = "tcaa.`updated_at` LIKE '%{$params['start_updated']}%'";
            } else {
                $filter[] = "tcaa.`updated_at` > '{$params['start_updated']}' AND tcaa.`updated_at` < '{$params['end_updated']} 23:59:59'";
            }
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = "tcaa.`auditstatus`={$params['status']}";
        }
        $where = '';
        if (count($filter) > 0) {
            $where .= ' WHERE ' . implode(' AND ', $filter);
        }

        $sql = "SELECT COUNT(*) FROM `gl_companies_account_apply` AS tcaa
                JOIN `gl_companies` ON gl_companies.`id`=tcaa.`companies_id`
                {$where}";
//        error_log($where, 3, 'sql_print.txt');
        $result['totalRow'] = $this->dbh->select_one($sql);
        if ($result['totalRow']) {
            if (isset($params['page']) && $params['page'] == false) {
                $sql = "SELECT gl_companies.id as c_id,gl_companies.company_name,
                        tcaa.id,tcaa.auditname,tcaa.auditstatus,tcaa.created_at,tcaa.updated_at
                        FROM `gl_companies_account_apply` AS tcaa
                        JOIN `gl_companies` ON gl_companies.`id`=tcaa.`companies_id`
                        {$where} {$orders}";
//                error_log($sql, 3, 'sql_print.txt');
                $result['list'] = $this->dbh->select($sql);
            } else {
                $result['totalPage'] = ceil($result['totalRow'] / $params['pageSize']);
                $this->dbh->set_page_num($params['pageCurrent']);
                $this->dbh->set_page_rows($params['pageSize']);
                $sql = "SELECT gl_companies.id as c_id,gl_companies.company_name,
                        tcaa.id,tcaa.auditname,tcaa.auditstatus,tcaa.created_at,tcaa.updated_at
                        FROM `gl_companies_account_apply` AS tcaa
                        JOIN `gl_companies` ON gl_companies.`id`=tcaa.`companies_id`
                        {$where} {$orders}";
//                error_log($sql, 3, 'sql_print.txt');
                $result['list'] = $this->dbh->select_page($sql);
            }
        }
        return $result;
    }

    public function getInfoById($id)
    {
        $sql = "SELECT gl_companies.id as c_id,gl_companies.company_name,tcaa.auditmemo,
                tcaa.id,tcaa.info_id,tcaa.auditname,tcaa.auditstatus,tcaa.created_at,tcaa.updated_at,tcaa.contactphone,tcaa.contactname,tcaa.mailaddress,tcaa.commaddress,tcaa.legalpersonname,tcaa.companyname,tcaa.certno as social_code
                FROM `gl_companies_account_apply` AS tcaa
                JOIN `gl_companies` ON gl_companies.`id`=tcaa.`companies_id`
                WHERE tcaa.`id`={$id}";

        $result = $this->dbh->select_row($sql);
        $c_id = $result['c_id'];
        $sql_filed = "SELECT * FROM gl_companies_account WHERE companies_id={$c_id}";
        $tca = $this->dbh->select_row($sql_filed);
        return array('result' => $result, 'tca' => $tca);
    }


    public function saveAccount($params, $id)
    {
        $result = $this->dbh->update('gl_companies_account', $params, 'id=' . $id);
        return $result;
    }

    public function addAccount($params)
    {
        $result = $this->dbh->insert('gl_companies_account', $params);
        return $result;
    }

    public function saveAccountApply($params, $id)
    {
        $result = $this->dbh->update('gl_companies_account_apply', $params, 'id=' . $id);
        return $result;
    }
}