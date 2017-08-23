<?php

/**
 * 询价单管理
 * User: Andy
 */
class Transmanage_InquiryDelModel
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

    public function getInquiryList($search){
        $filter = array();
        //创建起始时间
        if(isset($search['starttime']) && $search['starttime'] != ''){
            $filter[] = " l.`created_at` >= '{$search['starttime']} 00:00:00'";
        }
        //创建截止时间
        if(isset($search['endtime']) && $search['endtime'] != ''){
            $filter[] = " l.`updated_at` <= '{$search['starttime']} 23:59:59'";
        }
        //询价状态
        if(isset($search['status']) && $search['status'] != ''){
            $filter[] = " l.`status` = '{$search['status']} ";
        }
        //起始省
        if(isset($search['start_provice_id']) && $search['start_provice_id'] != ''){
            $filter[] = " g.`start_provice_id` ={$search['start_provice_id']} ";
        }
        //起始市
        if(isset($search['start_city_id']) && $search['start_city_id'] != ''){
            $filter[] = " g.`start_city_id` ={$search['start_city_id']} ";
        }
        //目的省
        if(isset($search['end_provice_id']) && $search['end_provice_id'] != ''){
            $filter[] = " g.`end_provice_id` ={$search['end_provice_id']} ";
        }
        //目的市
        if(isset($search['end_city_id']) && $search['end_city_id'] != ''){
            $filter[] = " g.`end_city_id` ={$search['end_city_id']} ";
        }
        //重量
        if(isset($search['min']) && $search['min'] != ''){
            $filter[] = " g.`weights` >= {$search['min']}";
        }
        if(isset($search['max']) && $search['max'] != ''){
            $filter[] = " g.`weights` <= {$search['max']}";
        }

        if(isset($search['cid']) && $search['cid'] != ''){
            $filter[] = " l.`cid` = {$search['cid']}";
        }

        $where = " WHERE g.`is_del` = 0 ";

        if(count($filter)>0){
            $where .= ' AND '.implode(' AND ', $filter);
        }
        //总数
        $sql = " SELECT count(1) FROM gl_inquiry WHERE cid = {$search['cid']}";
        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['list'] = array();

        $sql = " SELECT 
            l.`id`,
            l.`gid`,
            l.`status`,
            l.`cid`,
            l.`created_at`,
            g.`start_provice_id`,
            g.`start_city_id`,
            g.`end_provice_id`,
            g.`end_city_id`,
            g.`product_id`,
            g.`weights`,
            p.`zh_name`
            FROM
            gl_inquiry as l 
            LEFT JOIN gl_goods as g ON g.id = l.gid
            LEFT JOIN gl_products as p ON p.id = g.product_id
            {$where}
          ORDER BY l.`updated_at` DESC";

        $this->dbh->set_page_num($search['page'] ? $search['page'] : 1);
        $this->dbh->set_page_rows($search['rows'] ? $search['rows'] : 8);

        $result['list'] = $this->dbh->select_page($sql);

        if(!empty($result['list'])){
            $city = array_column($this->dbh->select('SELECT cityid,city FROM conf_city'),'city','cityid');
            foreach($result['list'] as $key=>$value){
                $result['list'][$key]['start_city'] = $city[$value['start_city_id']];
                $params['list'][$key]['end_city'] = $city[$value['end_city_id']];
            }
            unset($city);
        }

        return $result;
    }



    /*获取询价单基本信息*/
    public function getGoodsInfo($id){
        //goods基本信息
        $sql  = "SELECT gd.id, gd.cid ,gd.start_provice_id ,gd.start_city_id ,gd.end_provice_id ,gd.end_city_id ,gd.weights ,gd.price ,gd.companies_name ,gd.off_starttime ,gd.off_endtime ,gd.reach_starttime ,gd.reach_endtime ,gd.offer_status,gd.offer_price,gd.loss,gd.desc_str ,gd.off_address ,gd.off_user ,gd.off_phone ,gd.reach_address ,gd.reach_user ,gd.reach_phone ,gd.consign_user ,gd.consign_phone,gp.zh_name,gct.`name`,gd.created_at FROM gl_goods gd LEFT JOIN  gl_products gp ON gp.id = gd.product_id LEFT JOIN gl_cars_type gct ON  gct.id=gd.cars_type WHERE gd.id =".intval($id);
        $result['info'] = $this->dbh->select_row($sql);

        //获取市的信息
        $result['city'] = $this->dbh->select('SELECT cityid,city FROM conf_city');
        // //该记录 是否存在询价单信息
        // $result['exist'] = $this->dbh->select_one('select count(1) from gl_inquiry where gid = '.intval($id));
        return $result;


    }



}