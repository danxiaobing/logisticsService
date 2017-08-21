<?php
/**
 * User: Daley
 */
class Cargo_GoodsModel
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

    //列表
    public function getList($params)
    {
        $filter = $filed = array();
        $where = 'WHERE is_del = 0 ';
        $order = "updated_at";

        if (isset($params['order']) && $params['order'] != '') {
            $order = $params['order'];
        }
        if (isset($params['cid']) && $params['cid'] != '') {
            $filter[] = " cid = " . $params['cid'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $filter[] = " `status`=" . $params['status'];
        }
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }
        $result = array(
            'totalRow' => 0,
            'list' => array()
        );

        $sql = "SELECT count(1) FROM `gl_goods` {$where}";
        $result['totalRow'] = $this->dbh->select_one($sql);

        $this->dbh->set_page_num($params['page'] ? $params['page'] : 1);
        $this->dbh->set_page_rows($params['rows'] ? $params['rows'] : 15);

        $sql = "SELECT
               id,
               start_pid,
               end_pid,
               cate_id,
               product_id,
               weights,
               price,
               companies_name,
               off_starttime,
               off_endtime,
               reach_starttime,
               reach_endtime,
               status
               FROM gl_goods " . $where . "   ORDER BY `{$order}` DESC";
        $result['list'] = $this->dbh->select_page($sql);
        return $result;
    }
    /**
     * 根据id获取详情
     * id: 权限id
     * @return 数组
     */
    public function getInfo($id = 0)
    {
        $sql = "SELECT
               id,
               start_pid,
               end_pid,
               cate_id,
               product_id,
               weights,
               price,
               companies_name,
               off_starttime,
               off_endtime,
               reach_starttime,
               reach_endtime,
               cars_type,
               loss,
               off_address,
               off_user,
               off_phone,
               reach_address,
               reach_user,
               reach_phone,
               consign_user,
               consign_phone,
               desc_str,
               status
               FROM gl_goods WHERE id=".$id;

        return $this->dbh->select_row($sql);
    }
    //添加
    public function addInfo($params)
    {
        return $this->dbh->insert('gl_goods',$params);
    }

    //修改
    public function updata($params,$id)
    {
        return $this->dbh->update('gl_goods',$params,'id=' . intval($id));
    }

    //删除
    public function delete($id)
    {
        $data = [
            'is_del' => 1,
            'updated_at' => '=NOW()'
        ];
        return $this->dbh->update('gl_goods',$data,'id=' . intval($id));
    }

}
