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

    /*询价单列表*/
    public function getInquiryList($search){
        $filter = array(); 
        //创建起始时间
        if(isset($search['starttime']) && $search['starttime'] != ''){
            $filter[] = " gd.`created_at` >= '{$search['starttime']} 00:00:00'";
        }
        //创建截止时间
        if(isset($search['endtime']) && $search['endtime'] != ''){
            $filter[] = " gd.`updated_at` <= '{$search['starttime']} 23:59:59'";
        }
        //询价状态
        if(isset($search['status']) && $search['status'] != ''){
            $filter[] = '';
        }
        //起始省
        if(isset($search['pstart']) && $search['pstart'] != ''){
            $filter[] = " gd.`start_pid` ={$search['pstart']} ";
        }
        //起始市
        if(isset($search['cstart']) && $search['cstart'] != ''){
            $filter[] = " gd.`start_cid` ={$search['cstart']} ";
        }
        //目的省
        if(isset($search['pend']) && $search['pend'] != ''){
            $filter[] = " gd.`end_pid` ={$search['pend']} ";
        } 
        //目的市
        if(isset($search['cend']) && $search['cend'] != ''){
            $filter[] = " gd.`end_cid` ={$search['cend']} ";
        }  
        //重量
        if(isset($search['min']) && $search['min'] != ''){
            $filter[] = " gd.`weights` >= {$search['min']}";
        }               
        if(isset($search['max']) && $search['max'] != ''){
            $filter[] = " gd.`weights` <= {$search['max']}";
        }

        $WHERE = " WHERE gd.`is_del` = 0";

        if(count($filter)>0){
            $WHERE .= ' AND '.implode(' AND ', $filter);
        }
        //总数
        $sql = " SELECT count(1) FROM gl_goods gd {$WHERE}";
        $result['total'] = $this->dbh->select_one($sql);
        $result['list'] = array();

        if($result['total']){
            $sql = " SELECT  ";
        }


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