<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class App_Driver_PushlistModel
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


    /**
     * 消息推送列表
     * @param $params
     * @return mixed
     */
    public function getList($params){

        $where = ' 1= 1 AND is_del= 0 ';
        $filter = array();

        if (isset($params['driver_id']) && !empty($params['driver_id']) ) {
            $filter[] = " `driver_id` = ".intval($params['driver_id']);
        }else{
            $result['totalRow'] = 0;
            $result['totalPage'] = 0;
            $result['list'] = [];
            return $result;
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT count(1) FROM gl_message WHERE {$where}";
        $sql1 = 'SELECT count(1) FROM gl_message WHERE  status = 0 AND `driver_id` = '.intval($params['driver_id']);
        $unreadnums = $this->dbh->select_one($sql1);
        $rows = $params['rows'] ? $params['rows'] : 8;

        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['totalPage'] = (string)ceil($result['totalRow']/$rows);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($rows);

        $sql = "SELECT id as message_id,company_id,title,content,dispatch_id,dispatch_number,type,status,created_at FROM gl_message WHERE  {$where} ORDER BY id DESC";

        $result['list'] = $this->dbh->select_page($sql);
        $result['list']['unreadnums'] = $unreadnums;
        return $result;
    }

    /**
     * 删除消息
     * @param message_id 消息id
     */
    public function delMessage($message_id){

        if(empty($message_id)){
            return false;
        }
        $res = $this->dbh->update('gl_message',['is_del'=>1],' id ='.intval($message_id));

        if(!$res){
            return false;
        }
        return true;

    }


    /**
     * 未读消息列表
     * @param $params
     * @return mixed
     */
    public function unreadlist($params){

        $where = ' 1= 1 AND is_del= 0 AND status=0 ';
        $filter = array();

        if (isset($params['driver_id']) && !empty($params['driver_id']) ) {
            $filter[] = " `driver_id` = ".intval($params['driver_id']);
        }else{
            $result['totalRow'] = 0;
            $result['totalPage'] = 0;
            $result['list'] = [];
            return $result;
        }

        if (count($filter) > 0) {
            $where .= ' AND '.implode(" AND ", $filter);
        }

        $sql = "SELECT count(1) FROM gl_message WHERE {$where}";
        $rows = $params['rows'] ? $params['rows'] : 8;

        $result['totalRow'] = $this->dbh->select_one($sql);
        $result['totalPage'] = (string)ceil($result['totalRow']/$rows);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($rows);

        $sql = "SELECT id as message_id,company_id,title,content,dispatch_id,dispatch_number,type,status,created_at FROM gl_message WHERE  {$where} ORDER BY id DESC";
        $result['list'] = $this->dbh->select_page($sql);

        return $result;
    }




}
