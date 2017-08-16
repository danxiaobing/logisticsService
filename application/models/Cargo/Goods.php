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
    public function getlist($params,$cid)
    {
        if ($cid != 0) {
            $where = " WHERE is_del = 0 AND cid <> " . $cid;
        } else {
            $where = ' WHERE is_del = 0 ';
        }
        $filter = $filed = array();
        /*if (isset($params['type']) && $params['type'] != '') {
            $filter[] = " type = " . $params['type'];
        }
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }*/
        $sql = "SELECT id,start_id,end_id,cate_id,product_id,weights,price,companies_name,off_starttime,reach_starttime,status FROM gl_goods " . $where . " ORDER BY id DESC  ";
        $data =  $this->dbh->select($sql);

        return $data;
    }
    /**
     * 根据id获取详情
     * id: 权限id
     * @return 数组
     */
    public function getInfo($id = 0)
    {
        $sql = "SELECT * FROM gl_goods WHERE id=".$id;
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
