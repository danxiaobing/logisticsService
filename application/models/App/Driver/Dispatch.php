<?php

/**
 * 询价单管理
 * User: Jeff
 */
class App_Driver_DispatchModel
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
        if (isset($params['company_id']) && count($params['company_id']) ) {
            $filter[] = " god.`c_id` = ".$params['company_id'];
        }

        if (isset($params['user_id']) && $params['user_id'] != '' ) {
            $filter[] = "( god.`supercargo_id` = ".$params['user_id'] ." or  god.`driver_id` = ".$params['user_id']." )";
        }

        if (isset($params['ids']) && count($params['ids']) ) {
            $filter[] = " god.`id` in ({$params['ids']}) ";
        }

        if (isset($params['start_time']) && $params['start_time'] != '') {
            $filter[] = " god.`created_at` >= '".$params['start_time']." 00:00:00 '";
        }

        if (isset($params['end_time']) && $params['end_time'] != '') {
            $filter[] = " god.`created_at` <= '".$params['end_time']." 23:59:59 '";
        }

        if (isset($params['keyworks']) && $params['keyworks'] != '') {
            $filter[] = " ( god.`dispatch_number` like '%{$params['keyworks']}%' OR god.`cars_number` like '%{$params['keyworks']}%' OR god.`driver_name` like '%{$params['keyworks'] }%'  OR god.`supercargo_name` like '%{$params['keyworks']}%')";
        }

        if (isset($params['status']) && $params['status'] != '') {
            $filter[] = " god.`status` =".$params['status'];
        }
        if (isset($params['statusarr']) && $params['statusarr'] != '') {
            $filter[] = " god.`status` in (".$params['statusarr'].")";
        }

        $where = ' 1= 1 ';
        // print_r($filter);die;
        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT count(1) FROM gl_order_dispatch  as god LEFT JOIN gl_goods as g ON g.id = god.goods_id WHERE {$where}";
        // print_r($sql);die;
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 8);

        $sql = "SELECT 
                god.id,
                god.dispatch_number,
                god.order_number,
                god.c_name,
                god.start_provice_id,
                god.end_provice_id,
                god.start_city_id,
                god.end_city_id,
                god.ctype_name,
                god.driver_name,
                god.supercargo_name,
                god.cars_number,
                god.end_time,
                god.start_time,
                god.weights,
                god.start_weights,
                god.end_weights,
                god.status,
                g.start_provice,
                g.start_city,
                g.end_provice,
                g.end_city,
                g.companies_name,
                g.product_id,
                g.product_name,
                g.loss,
                g.desc_str,
                g.off_address,
                g.off_user,
                g.off_phone,
                g.reach_user,
                g.reach_phone,
                g.reach_address,
                g.consign_user,
                g.consign_phone
                FROM gl_order_dispatch as god
                LEFT JOIN gl_goods as g ON g.id = god.goods_id
                WHERE  {$where}
                ORDER BY id ASC 
                ";
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }

    /**
     * 获取调度单信息
     */
    public function getDispatchInfoById($id){
        if(intval($id)!=0){
            $dispatchData = $this->dbh->select_row('SELECT order_id,c_id,status,weights,start_weights,end_weights,goods_id FROM gl_order_dispatch WHERE id = '.intval($id));
            return $dispatchData;
        }
        return false;
    }

    /**
     * 更新调度单信息
     * @param $params
     * @return bool
     */
    public function dispatchProcedure($params){
        $dispatch_arr  = [
            'status' =>$params['status'],
            'start_weights'=>$params['start_weights'],
            'end_weights'=>$params['end_weights'],
        ];

        #过滤空值
        $dispatch_arr = array_filter($dispatch_arr);

        $dispatchData = $this->dbh->select_row('SELECT order_id,c_id,status,weights,start_weights,end_weights,goods_id FROM gl_order_dispatch WHERE id = '.intval($params['id']));

        //查询该调度单调度日志是否已调度
        $dispatch_log = $this->dbh->select_row('SELECT * FROM gl_order_dispatch_log WHERE is_del=0 AND dispatch_id = '.$params['id'] .' AND status ='. $dispatch_arr['status']);
    
        if($dispatch_log){
            return false;
        }

        #开启事物
        $this->dbh->begin();
        try{
            #修改调度单
            $dispatch = $this->dbh->update('gl_order_dispatch', $dispatch_arr,'id = '.intval($params['id']));


            if(!$dispatch){
                $this->dbh->rollback();
                return false;
            }

            #插入日志表
            $dispatch_log = $this->dbh->insert('gl_order_dispatch_log',['status'=>intval($params['status']),'dispatch_id'=>$params['id'],'created_at'=>'=NOW()','updated_at'=>'=NOW()']);
            if(!$dispatch_log){
                $this->dbh->rollback();
                return false;
            }

            if(1 == $params['status']){
                $goods_arr = $this->dbh->select_row('SELECT weights,weights_done FROM gl_goods WHERE id = '.$dispatchData['goods_id']);
                $status = $goods_arr['weights'] == $goods_arr['weights_done'] ? 3:8;
                $order = $this->dbh->update('gl_order',['status'=>$status],' id ='.$dispatchData['order_id']);
                if(empty($order)){
                    $this->dbh->rollback();
                    return false;
                }

            }

            if(5 == $params['status']){
                $count = $this->dbh->select_one('SELECT COUNT(1) FROM gl_order_dispatch WHERE status != 6 AND order_id = '.$dispatchData['order_id']);
                $completeTotal =  $this->dbh->select_one('SELECT COUNT(1) FROM gl_order_dispatch WHERE status = 5  AND order_id = '.$dispatchData['order_id']);
                $status = $this->dbh->select_row('SELECT status FROM gl_order WHERE id ='.$dispatchData['order_id']);
                if($count == $completeTotal && $status['status'] == 3){
                    $goods = $this->dbh->update('gl_goods',['status'=>6],' id = '.$dispatchData['goods_id']);
                    $order = $this->dbh->update('gl_order',['status'=>4],' id ='.$dispatchData['order_id']);
                    if(empty($goods) or empty($order)){
                        $this->dbh->rollback();
                        return false;
                    }
                }
            }

            #卸货和装货要上传图片
            if(5 == $params['status'] or 3 == $params['status']){
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


            #取消调度单时候改重量
            if(6 == $params['status']){
                $goods_arr = $this->dbh->select_row('SELECT weights_done FROM gl_goods WHERE id = '.$dispatchData['goods_id']);
                $goods = $this->dbh->update('gl_goods',['weights_done'=>$goods_arr['weights_done'] - $dispatchData['weights']],' id = '.$dispatchData['goods_id']);
                $count = $this->dbh->select_one('SELECT COUNT(1) FROM gl_order_dispatch WHERE status > 0  AND status  not in(6,7)   AND order_id = '.$dispatchData['order_id']);

                if(!empty($count)){
                    $status= 8;
                }else{
                    $dispatchCount = $this->dbh->select_one('SELECT COUNT(1) FROM gl_order_dispatch WHERE status = 0  AND status  not in(6,7)   AND order_id = '.$dispatchData['order_id']);
                    $status = !empty($dispatchCount) ? 2 : 1;
                }

                $order = $this->dbh->update('gl_order',['status'=>$status],' id ='.$dispatchData['order_id']);
                if(!$goods && !$order){
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
     * 查询调运单列表
     * @param $id
     * @return array
     */
    public function dispatchList($id){
        $sql = "SELECT id,dispatch_number,weights,cars_number,driver_name,supercargo_name,start_time,end_time FROM gl_order_dispatch WHERE status = 5 AND order_id = ".intval($id);
        return $this->dbh->select($sql);
    }

    /**
     * 查询调运单列表
     * @param $id
     * @return array
     */
    public function getListByOrderid($id){
        $sql = "SELECT id,dispatch_number,weights,cars_number,driver_name,supercargo_name,start_time,end_time FROM gl_order_dispatch WHERE status != 6 AND order_id = ".intval($id);
        return $this->dbh->select($sql);
    }

    //获取代办
    public function getNeedList($id){
        $sql = "SELECT id,dispatch_number,weights,cars_number,driver_name,supercargo_name,start_time,end_time FROM gl_order_dispatch WHERE status != 6 AND order_id = ".intval($id);
        return $this->dbh->select($sql);
    }

    /*
     * 调度单详情
     * @params $dispatch_id  调度单id
     * @params $dispatch_number 调度单号
     * @return array
    */
    public function getInfo($params){

        if(isset($params['dispatch_id']) && !empty($params['dispatch_id'])){
            $filter[]  = "god.id=".intval($params['dispatch_id']);
        }
        if(isset($params['dispatch_number']) && !empty($params['dispatch_number'])){
            $filter[]  = 'god.dispatch_number="'.$params['dispatch_number'].'"';
        }
        $where = ' 1= 1 ';

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT
                god.id,
                god.dispatch_number,
                god.order_number,
                god.c_name,
                god.start_provice_id,
                god.end_provice_id,
                god.start_city_id,
                god.end_city_id,
                god.ctype_name,
                god.driver_name,
                god.supercargo_name,
                god.cars_number,
                god.end_time,
                god.start_time,
                god.weights,
                god.start_weights,
                god.end_weights,
                god.cars_id,
                god.driver_id,
                god.supercargo_id,
                god.ctype_id,
                god.status,
                g.companies_name,
                g.product_id,
                g.loss,
                g.desc_str,
                g.off_address,
                g.off_user,
                g.off_phone,

                g.off_starttime as actually_start_time,
                g.reach_starttime as actually_end_time,
                g.start_provice as provincestart,
                g.start_city as citystart,
                g.start_area,
                g.end_provice as provinceend,
                g.end_city as cityend,
                g.end_area,

                g.reach_user,
                g.reach_phone,
                g.reach_address,
                g.consign_user,
                g.consign_phone
                FROM gl_order_dispatch as god
                LEFT JOIN gl_goods as g ON g.id = god.goods_id
                WHERE  ".$where;

        $data =  $this->dbh->select_row($sql);
        if(!empty($data)){

            //更新消息通知状态为已读
            if(isset($params['is_push']) && !empty($params['is_push'])){
                $this->dbh->update('gl_message', array('status'=>1),'dispatch_id = "'.$data['id'].'"');
            }

        }

        return array('info'=>$data);

    }

    /**
     * 编辑和新增
     * @param  array $params
     * @return bool
     */
    public function editDispatch($params){
        if(!empty($params['id'])){
            $res = $this->dbh->update('gl_order_dispatch', $params,' id = '.intval($params['id']).' AND c_id = '.intval($params['c_id']));
            if($res){
                return true;
            }else{
                return false;
            }
        }else{
            #分配每次调度的重量
            $time = $params['time'];
            $weights_this = $params['weights_this'];
            $weights_done = $params['weights_done'];
            $weights_all = $params['weights_all'];
            $weights_every = sprintf("%.3f", $weights_this/count($time));
            $weights_remainder = $weights_this == ($weights_every*count($time))?0:$weights_this - ($weights_every*count($time));
            $weights_remainder = $weights_every+sprintf("%.3f", $weights_remainder);
            unset($params['weights_this']);
            unset($params['weights_done']);
            unset($params['weights_all']);
            unset($params['time']);
            if(empty($time)){
                return false;
            }

            $this->dbh->begin();
            try{

                #循环插入调度单 根据次数
                foreach ($time as $key=>$v){
                    if($key == 0){
                        $params['weights'] = $weights_remainder;
                    }else{
                        $params['weights'] = $weights_every;
                    }
                    $params['start_time'] = $v['start_time'];
                    $params['end_time'] = $v['end_time'];
                    $params['dispatch_number'] = 'DD'.time().rand(100,999).$key;

                    $res = $this->dbh->insert('gl_order_dispatch', $params);
                    if(!$res){
                        $this->dbh->rollback();
                        return false;
                    }
                }

                if($weights_done == 0 ){
                    $order = $this->dbh->update('gl_order',['status'=>2],' id ='.intval($params['order_id']));
                    if(!$order){
                        $this->dbh->rollback();
                        return false;
                    }
                }

                #修改这次已调度重量
                $weights_done =  $weights_done+$weights_this;
                $goods = $this->dbh->update('gl_goods',['weights_done'=>$weights_done],' id ='.$params['goods_id']);
                if(!$goods){
                    $this->dbh->rollback();
                    return false;
                }

                #判断总吨数和已调度吨数 （修改状态）
                if($weights_all == $weights_done){
                    $goods = $this->dbh->update('gl_goods',['weights_done'=>$weights_done],' id ='.$params['goods_id']);
                    $count = $this->dbh->select_one('SELECT COUNT(1) FROM gl_order_dispatch WHERE status not in(6,7) AND status > 0  AND order_id = '.$params['order_id']);
                    if(!empty($count)){
                        $order = $this->dbh->update('gl_order',['status'=>3],' id ='.intval($params['order_id']));
                        if(!$order){
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

    /*确认是否符合发车条件*/
    public function queryInfo($dispatch_id){
        //根据调度单id获取goosid
        $sql = "SELECT go.goods_id FROM gl_order_dispatch god LEFT JOIN gl_order go ON go.id = god.order_id WHERE god.id=".intval($dispatch_id);
        $res = $this->dbh->select_one($sql);
        if(!$res){
            return false;
        }

        //判断状态
        $sql = "SELECT weights,weights_done FROM gl_goods WHERE  id=".$res;
        $data = $this->dbh->select_row($sql);
        if($data['weights'] == $data['weights_done'] && $data['weights'] != 0){
            return true;
        }else{
            return false;
        }
    }

    public function dispatchPic($id,$status){
        $pic = $this->dbh->select('SELECT pic FROM  gl_order_dispatch_pic WHERE dispatch_id = '.intval($id).' AND status = '.intval($status));
        return $pic;
    }



}