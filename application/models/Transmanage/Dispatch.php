<?php

/**
 * 询价单管理
 * User: Jeff
 */
class Transmanage_DispatchModel
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

    public function searchOrder($params){
        $filter = array();

        if (isset($params['start_provice_id']) && $params['start_provice_id'] != '') {
            $filter[] = " g.`start_provice_id` =".$params['start_provice_id'];
        }

        if (isset($params['start_city_id']) && $params['start_city_id'] != '') {
            $filter[] = " g.`start_city_id` =".$params['start_city_id'];
        }

        if (isset($params['end_provice_id']) && $params['end_provice_id'] != '') {
            $filter[] = " g.`end_provice_id` =".$params['end_provice_id'];
        }

        if (isset($params['end_city_id']) && $params['end_city_id'] != '') {
            $filter[] = " g.`end_city_id` =".$params['end_city_id'];
        }


        if (isset($params['starttime']) && $params['starttime'] != '') {
            $filter[] = " o.`starttime` <= '{$params['starttime']}'";
        }

        if (isset($params['endtime']) && $params['endtime'] != '') {
            $filter[] = " o.`off_starttime` >= '{$params['endtime']}'";
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " o.`reach_starttime` = '{$params['status']}'";
        }

        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " o.`company_id` = '{$params['cid']}'";
        }

        $where = ' o.`is_del` = 0 ';

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT count(1) FROM gl_order AS o LEFT JOIN gl_goods AS g ON g.`id` = o.`goods_id`  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

        $sql = "SELECT 
               g.start_provice_id,
               g.start_city_id,
               g.end_provice_id,
               g.end_city_id,
               g.cate_id,
               g.product_id,
               g.weights,
               g.companies_name,
               g.off_starttime,
               g.off_endtime,
               g.reach_starttime,
               g.reach_endtime,
               o.status,
               o.id,
               o.created_at,
               o.number,
               p.zh_name
                FROM gl_order as o 
                LEFT JOIN gl_goods as g ON g.id = o.goods_id
                LEFT JOIN gl_products as p ON p.id = g.product_id
                WHERE  {$where}
                ORDER BY id DESC 
                ";

        $result['list'] = $this->dbh->select_page($sql);

        if(!empty($result['list'])){
            $city = array_column($this->dbh->select('SELECT cityid,city FROM conf_city'),'city','cityid');
            foreach($result['list'] as $key=>$value){
                $result['list'][$key]['start_city'] = $city[$value['start_city_id']];
                $result['list'][$key]['end_city'] = $city[$value['end_city_id']];
            }
            unset($city);
        }

        return $result;
    }

    public function dispatchProcedure($params){
        $dispatch_arr  = [
            'id' =>$params['id'],
            'status' =>$params['status'],
            'start_weights'=>$params['start_weights'],
            'end_weights'=>$params['end_weights'],
            'start_time'=>$params['start_time'],
            'end_time'=>$params['end_time'],
            'weights'=>$params['weights'],
        ];

        $dispatch_arr = array_filter($dispatch_arr);

        #开启事物
        $this->dbh->begin();
        try{
            #修改调度单
            $dispatch = $this->dbh->update('gl_order_dispatch', $dispatch_arr,'id = '.intval($params['id']));
            if(!$dispatch){
                $this->dbh->rollback();
                return false;
            }

            $dispatch_log = $this->dbh->insert('gl_order_dispatch_log',['status'=>intval($params['status']),'dispatch_id'=>$params['id'],'created_at'=>'=NOW()','updated_at'=>'=NOW()']);
            if(empty($dispatch_log)){
                $this->dbh->rollback();
                return false;
            }

            if(5 == $params['status'] && 3 == $params['status']){
                if(empty($params['other_file'])){
                    $this->dbh->rollback();
                    return false;
                }
                $pic = [];
                $status = $params['status'] == 3?1:2;
                foreach ($params['other_file'] as $key=>$v){
                    $pic[$key] = ['pic'=>$v,'status'=>$status,'dispatch_id'=>$params['id'],'created_at'=>'=NOW()','updated_at'=>'=NOW()'];
                }

                $data = '';
                foreach ($pic as $v){
                    $data = $this->dbh->insert('gl_order_dispatch_pic',$v);
                    if(empty($data)){
                        $this->dbh->rollback();
                        return false;
                    }
                }
            }

            $this->dbh->commit();
            return true;

        } catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }
}