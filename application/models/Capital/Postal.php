<?php

/**
 * Created by PhpStorm.
 * User: xingjun
 * Date: 2017/3/21
 * Time: 10:30
 */
class Capital_PostalModel
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

    public function getPostalList($params)
    {   //排序 ‘别名’
        if (isset($params['orders']) && $params['orders'] != '') {
            $ord = explode(",", $params['orders']);
            $ord_str = "pay_takemoney." . implode(",pay_takemoney.", $ord);
            $ord_str = str_replace("pay_takemoney.company_name", "td_companies.company_name", $ord_str);
            $ord_str = str_replace("pay_takemoney.user_name", "td_user_info.user_name", $ord_str);
            $orders = "ORDER BY " . $ord_str;
        } else {
            $orders = "ORDER BY pay_takemoney.updated_at DESC,pay_takemoney.status DESC";
        }
        //创建日期筛选
        $sin_start = date('Y-m-d H:i:s', strtotime($params['start_created']));
        $sin_end = date('Y-m-d H:i:s', strtotime($params['end_created']) + 3600 * 24);
        if (isset($params['start_created']) && $params['start_created'] != '' && isset($params['end_created']) && $params['end_created'] != '') {
            if (date('Y-m-d H:i:s', strtotime($params['start_created'])) == date('Y-m-d H:i:s', strtotime($params['end_created']))) {
                $filter[] = "pay_takemoney.`created_at` LIKE '%{$params['start_created']}%'";
            } else {
                $filter[] = "pay_takemoney.`created_at` > '{$sin_start}' AND pay_takemoney.`created_at` < '{$sin_end}'";
            }
        }
        //编辑日期筛选
        $sin_start1 = date('Y-m-d H:i:s', strtotime($params['start_updated']));
        $sin_end1 = date('Y-m-d H:i:s', strtotime($params['end_updated']) + 3600 * 24);
        if (isset($params['start_updated']) && $params['start_updated'] != '' && isset($params['end_updated']) && $params['end_updated'] != '') {
            if (date('Y-m-d H:i:s', strtotime($params['start_updated'])) == date('Y-m-d H:i:s', strtotime($params['end_updated']))) {
                $filter[] = "pay_takemoney.`updated_at` LIKE '%{$params['start_updated']}%'";
            } else {
                $filter[] = "pay_takemoney.`updated_at` > '{$sin_start1}' AND pay_takemoney.`updated_at` < '{$sin_end1}'";
            }
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = "pay_takemoney.`status`={$params['status']}";
        }
        $where = '';
        if (count($filter) > 0) {
            $where .= ' WHERE ' . implode(' AND ', $filter);
        }

        $sql = "SELECT COUNT(*) FROM `pay_takemoney`
                LEFT JOIN `td_companies` ON td_companies.`id`=pay_takemoney.`companies_id`
                LEFT JOIN `td_user_info` ON td_user_info.`id`=pay_takemoney.`info_id`{$where}";
//        error_log($where, 3, 'sql_print.txt');
        $result['totalRow'] = $this->dbh->select_one($sql);
        if ($result['totalRow']) {
            if (isset($params['page']) && $params['page'] == false) {
                $sql = "SELECT pay_takemoney.*,td_companies.company_name,td_user_info.user_name
                        FROM `pay_takemoney`
                        LEFT JOIN `td_companies` ON td_companies.`id`=pay_takemoney.`companies_id`
                        LEFT JOIN `td_user_info` ON td_user_info.`id`=pay_takemoney.`info_id`{$where} {$orders}";
//                error_log($sql, 3, 'sql_print.txt');
                $result['list'] = $this->dbh->select($sql);
            } else {
                $result['totalPage'] = ceil($result['totalRow'] / $params['pageSize']);
                $this->dbh->set_page_num($params['pageCurrent']);
                $this->dbh->set_page_rows($params['pageSize']);
                $sql = "SELECT pay_takemoney.*,td_companies.company_name,td_user_info.user_name
                        FROM `pay_takemoney`
                        LEFT JOIN `td_companies` ON td_companies.`id`=pay_takemoney.`companies_id`
                        LEFT JOIN `td_user_info` ON td_user_info.`id`=pay_takemoney.`info_id`{$where} {$orders}";
//                error_log($sql, 3, 'sql_print.txt');
                $result['list'] = $this->dbh->select_page($sql);
            }
        } else {
            $result = [];
        }
        return $result;
    }

    public function getPostalInfo($id)
    {
        $sql = "SELECT pay_takemoney.*,td_companies.company_name,td_user_info.user_name
                FROM `pay_takemoney`
                LEFT JOIN `td_companies` ON td_companies.`id`=pay_takemoney.`companies_id`
                LEFT JOIN `td_user_info` ON td_user_info.`id`=pay_takemoney.`info_id` WHERE pay_takemoney.`id`={$id}";
        $result = $this->dbh->select_row($sql);
        return $result;
    }

    public function save($params, $pri_id)
    {
        $result = $this->dbh->update('pay_takemoney', $params, 'id=' . $pri_id);
        return $result;
    }

}