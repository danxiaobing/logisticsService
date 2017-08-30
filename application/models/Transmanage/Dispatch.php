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

    public function getList($params){
        $filter = array();


        if (isset($params['company_ids']) && count($params['company_ids']) ) {
            $filter[] = " `c_id` in (".implode(',',$params['company_ids']).")";
        }

        if (isset($params['start_time']) && $params['start_time'] != '') {
            $filter[] = " `start_time =".$params['start_time'];
        }

        if (isset($params['end_time']) && $params['end_time'] != '') {
            $filter[] = " `end_time` =".$params['end_time'];
        }

        if (isset($params['keyworks']) && $params['keyworks'] != '') {
            $filter[] = " `keyworks` =".$params['keyworks'];
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " `status` =".$params['status'];
        }

        $where = ' 1= 1 ';

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT count(1) FROM gl_order_dispatch  WHERE {$where}";

        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

        $sql = "SELECT 
               *
                FROM gl_order_dispatch
                WHERE  {$where}
                ORDER BY id DESC 
                ";
        $result['list'] = $this->dbh->select_page($sql);
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
        ];

        $dispatch_arr = array_filter($dispatch_arr);

        #开启事物
        $this->dbh->begin();
        try{
            #修改调度单
            $dispatch = $this->dbh->insert('gl_order_dispatch', $dispatch_arr,'id = '.intval($params['id']));
            if(!$dispatch){
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