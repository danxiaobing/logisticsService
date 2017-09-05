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
            $filter[] = " l.`updated_at` <= '{$search['endtime']} 23:59:59'";
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

        //起始县
        if(isset($search['start_area_id']) && $search['start_area_id'] != ''){
            $filter[] = " g.`start_area_id` ={$search['start_area_id']} ";
        } 

        //目的省
        if(isset($search['end_provice_id']) && $search['end_provice_id'] != ''){
            $filter[] = " g.`end_provice_id` ={$search['end_provice_id']} ";
        }
        //目的市
        if(isset($search['end_city_id']) && $search['end_city_id'] != ''){
            $filter[] = " g.`end_city_id` ={$search['end_city_id']} ";
        }

        //目的县
        if(isset($search['end_area_id']) && $search['end_area_id'] != ''){
            $filter[] = " g.`end_area_id` ={$search['end_area_id']} ";
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
                $result['list'][$key]['end_city'] = $city[$value['end_city_id']];
            }
            unset($city);
        }

        return $result;
    }



    /*获取询价单基本信息*/
    public function getGoodsInfo($id){
        //goods基本信息
        $sql  = "SELECT gd.id, gd.cid ,gd.start_provice_id ,gd.start_city_id ,gd.end_provice_id ,gd.end_city_id ,gd.weights ,gd.price ,gd.companies_name ,gd.off_starttime ,gd.off_endtime ,gd.reach_starttime ,gd.reach_endtime ,gd.offer_status,gd.offer_price,gd.loss,gd.desc_str ,gd.off_address ,gd.off_user ,gd.off_phone ,gd.reach_address ,gd.reach_user ,gd.reach_phone ,gd.consign_user ,gd.consign_phone,gp.zh_name,gct.`name`,gd.created_at,gd.`status` FROM gl_goods gd LEFT JOIN  gl_products gp ON gp.id = gd.product_id LEFT JOIN gl_cars_type gct ON  gct.id=gd.cars_type WHERE gd.id =".intval($id);
        $result['info'] = $this->dbh->select_row($sql);

        //获取市的信息
        $result['city'] = $this->dbh->select('SELECT cityid,city FROM conf_city');
        return $result;

    }


    /*获取当前询价单的价格状态信息*/
    public function getInquiryInfo($id){
        $sql = "SELECT gi.`id`,gi.`status`,gi.`type`,gii.`minprice`,gii.`maxprice`,gii.`type`,gii.`created_at` FROM gl_inquiry gi LEFT JOIN gl_inquiry_info gii ON gi.id = gii.pid WHERE gii.pid=".intval($id)." AND gi.`is_del`=0  ORDER BY gii.`id` ASC";
        $data =  $this->dbh->select($sql);
        // unset($data[0]);
        return $data;
    }

    /*生成询价单信息、询价日志*/
    public function addReceipt($data,$price,$goodsid){ 
        //判断是否需要生成询价单
        if(!empty($data)){
            //事务
            $this->dbh->begin();
            try{
                //生成询价单 同时更新状态为等待货主报价
                $data['status'] = 2;
                $id = $this->dbh->insert('gl_inquiry',$data);

                if(!$id){
                    $this->dbh->rollback();
                    return false;
                } 


                //生成询价日志信息 记录托运方报价
                // $info = array(
                //     'pid'        => $id,//询价单主键id
                //     'minprice'   => $price['offer_price'],
                //     'type'       => 2,
                //     'created_at' => '=NOW()',
                //     'updated_at' => '=NOW()',
                // );
                // $res = $this->dbh->insert('gl_inquiry_info',$info);


                //插入承运商报价
                $info['pid']      = $id;
                $info['minprice'] = $price['minprice'];
                $info['maxprice'] = $price['maxprice'];
                $info['type']     = 1;
                $info['created_at'] = '=NOW()';
                $info['updated_at'] = '=NOW()';

                $result = $this->dbh->insert('gl_inquiry_info',$info);
                if(!$result){
                    $this->dbh->rollback();
                    return false;                    
                }

                //更改goods表的询价状态
                $status = $this->dbh->update('gl_goods',array('status' => 2),'id='.intval($goodsid));
                if(!$status){
                    $this->dbh->rollback();
                    return false;                    
                }                
                $this->dbh->commit();
                return $id;


            }catch (Exception $e) {
                $this->dbh->rollback();
                return false;
            }
        }else{
            //直接记录询价日志
            $this->dbh->begin();
            try{
                $price['type'] = 1;
                $price['created_at'] = '=NOW()';
                $price['updated_at'] = '=NOW()';
                $id = $this->dbh->insert('gl_inquiry_info',$price);
                if(!$id){
                    $this->dbh->rollback();
                    return false;
                }
                //更新询价状态
                $status = $this->dbh->update('gl_inquiry',array('status' => 2),'id='.intval($price['pid']));
                if(!$status){
                    $this->dbh->rollback();
                    return false;                    
                }                
                $this->dbh->commit();
                return $price['pid'];

            }catch (Exception $e){
                $this->dbh->rollback();
                return false;                
            }
        }
    }

    public function cancalInquiry($params)
    {
        if (isset($params['id']) && $params['id'] != '') {
            $filter[] = " gl_inquiry.`id` = {$params['id']}";
        }

        if(isset($params['cid']) && $params['cid'] != ''){
            $filter[] = " gl_inquiry.`cid` = {$params['cid']}";
        }

        $where = " gl_inquiry.`is_del` = 0 ";

        if(count($filter)>0){
            $where .= ' AND '.implode(' AND ', $filter);
        }

        $sql = "SELECT gl_inquiry.`status`,gl_inquiry.`gid`,gl_inquiry.`car_id`,gl_goods.`reach_endtime`
                FROM gl_inquiry  
                LEFT JOIN gl_goods ON gl_goods.`id` = gl_inquiry.`gid`
                WHERE  
                {$where}";

        $inquiry = $this->dbh->select_row($sql);


        if(!$inquiry){
            return false;
        }


        $this->dbh->begin();

        try{
            $inquiry_up['status'] = 4;
            $inquiry_up['type']  = $params['type'];

            $result = $this->dbh->update('gl_inquiry',$inquiry_up,$where);

            if(!$result){
                $this->dbh->rollback();
                return false;
            }
            $goods['status']  = time() > strtotime($inquiry['reach_endtime']) ? 3:1;
            $data = $this->dbh->update('gl_goods',$goods,'id ='.$inquiry['gid']);
            if(empty($data)){
                $this->dbh->rollback();
                return false;
            }
            //不为空代表是回程车信息
            if(!empty($inquiry['car_id'])){
                //修改回程车信息状态

                $sql = "SELECT `id`,`end_time` FROM gl_return_car WHERE `id` =".$inquiry['car_id'];
                $return_car = $this->dbh->select_row($sql);
                if(!$return_car){
                    $this->dbh->rollback();
                    return false;
                }
                $info['status']  = time() > strtotime($return_car['end_time']) ? 3:1;
                $info['inquiry_id'] = 0;
                $info['order_id']  = 0;
                $result = $this->dbh->update('gl_return_car',$info,'id ='.$return_car['id']);
                if(!$result){
                    $this->dbh->rollback();
                    return false;
                }
            }

            $this->dbh->commit();
            return true;

        }catch (Exception $e){
            $this->dbh->rollback();
            return false;
        }
    }


    /*同意交易*/
    public function agreeInquiry($data,$inquiryid){
        //开启事务
        $this->dbh->begin();
        try{
            //同意交易流程
            if( $inquiryid == 0){

                //承运商未报价直接同意交易
                $input = array(
                    'gid'        => $data['goodsid'],//goodsid
                    'price'      => $data['price'],//成交价格
                    'cid'        => $data['cid'],//承运商id
                    'status'     => 3,//托运生成状态
                    'created_at' => '=NOW()',
                    'updated_at' => '=NOW()',
                );

                $id = $this->dbh->insert('gl_inquiry',$input);
                if(!$id){
                    $this->dbh->rollback();
                    return false;   
                }
                $price = $data['price'];

            }elseif($inquiryid != 0){

                //获取询价单相关信息
                $where = " gl_inquiry.`is_del` = 0 AND gl_inquiry.`id` = {$inquiryid}";
                $sql = "SELECT gl_inquiry.`status`,gl_inquiry.`gid`,gl_inquiry.`car_id` FROM gl_inquiry WHERE {$where}";
                $inquiry = $this->dbh->select_row($sql);
                if(!$inquiry){
                    $this->dbh->rollback();
                    return false;
                }
                //已生成询价单  同意交易
                $sql = "SELECT minprice FROM gl_inquiry_info WHERE pid=".intval($inquiryid)." AND type=2  ORDER BY id DESC LIMIT 1";
                $price  = $this->dbh->select_one($sql);//获取成交价格
                $res = $this->dbh->update('gl_inquiry',array('status' => 3,'price'=>$price),'id='.intval($inquiryid));

                if(!$res){
                    $this->dbh->rollback();
                    return 1111;
                }
                $id = $inquiryid;


            }

            /*生成托运单流程start*/
            list($min,$sec) = explode(" ",microtime());
            $orderid = date("Ymd"). substr($sec,3).mt_rand(100,999);


            //生成托运单
            $order_info = array(
                'number' => $orderid,
                'cargo_id'=> $data['companyid'],//货主公司id
                'goods_id' =>$data['goodsid'],
                'car_id' =>$inquiry['car_id'],
                'company_id' => $data['cid'],//承运商公司id
                'estimate_freight' =>  $price*$data['weights'],
                'updated_at'=>'=NOW()',
                'created_at'=>'=NOW()'
            );
            //car_id不为零时说明是货主找车 回程车议价添加的
            if(!empty($inquiry['car_id'])){
                $order_info['car_id'] = $inquiry['car_id'];

            }
            $resid = $this->dbh->insert('gl_order',$order_info);  

            if(!$resid){
                $this->dbh->rollback();
                return false;
            }
            //car_id不为零时说明是货主找车 回程车议价添加的  修改回程车信息
            if(!empty($inquiry['car_id'])){

                //修改回程车信息状态
                $info['status']  = 6;//已生成托运单
                $info['order_id']  = $resid;//已生成托运单
                $result = $this->dbh->update('gl_return_car',$info,'id ='.$inquiry['car_id']);
                if(!$result){
                    $this->dbh->rollback();
                    return false;
                }
            }

            //更新询价单 托运单：order_id=$resid
            $info['status']=3;
            $info['order_id']=$resid;
            $res = $this->dbh->update('gl_inquiry',$info,'id='.intval($id));
            if(!$res){
                $this->dbh->rollback();
                return false;                
            }


            //修改goods表状态
            $this->dbh->update('gl_goods',array('status'=>4),'id='.intval($data['goodsid']));  
            $this->dbh->commit();
            return $id;     
            /*生成托运单流程end*/
            
        }catch (Excaption $e){
            $this->dbh->rollback();
            return false;
        }
    }

}