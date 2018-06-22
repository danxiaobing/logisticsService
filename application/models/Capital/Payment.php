<?php
/**
 * Created by PhpStorm.
 * User: amor
 * Date: 2018/6/21
 * Time: 10:30
 */
class Capital_PaymentModel
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

    /**
     * 获取资金列表
     * @param array $params
     */
    public function getPaymentList($params){

        $filter = array();

        $where = 'p.isdel = 0 AND p.company_id = '.intval($params['carrier_id']);

        if (isset($params['company_name']) && !empty($params['company_name'])) {
            $filter[] = " p.`pay_companyname` =".$params['company_name'];
        }

        if (isset($params['paystatus']) && $params['paystatus'] != '') {
            $filter[] = " p.`paystatus` =".$params['paystatus'];
        }

        if (isset($params['start_time']) && $params['start_time'] != '') {
            $filter[] = " `createdat` >= '".$params['start_time']."'";
        }

        if (isset($params['end_time']) && $params['end_time'] != '') {
            $filter[] = " `updatedat` <= '".$params['end_time']."'";
        }

        #条件
        if (0 != count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }

        $countSql = "SELECT COUNT(1) FROM payment_master as p WHERE  {$where}";
        // return $sql  ;
        $result['totalRow'] = $this->dbh->select_one($countSql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

        $sql = "SELECT 
               *
                FROM payment_master as p
                WHERE  {$where}
                ORDER BY id DESC 
                ";

        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }


    public function getPaymentDetail($id){
    }


}