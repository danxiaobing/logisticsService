<?php
/**
 * User: Daley
 */
class Cargo_AddressModel
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

    //获取地址列表
    public function getCargoAddreslist($params)
    {

        $where = " WHERE is_del = 0 ";

        $filter = $filed = array();
        if (isset($params['type']) && $params['type'] != '') {
            $filter[] = " type = " . $params['type'];
        }
        if (isset($params['uid']) && !empty($params['uid'])) {
            $filter[] = " uid = " . $params['uid'];
        }
        if (isset($params['cid']) && !empty($params['cid'])) {
            $filter[] = " cid = " . $params['cid'];
        }

        if (isset($params['key']) && $params['key'] != '') {
            $filter[] = "
                (
                    `name` LIKE '%" .trim($params['key']). "%'
                    OR `mobile` LIKE '%" .trim($params['key']). "%'

                )";
        }
        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }
        $sql = "SELECT id,uid,name,mobile,address,remark,type FROM gl_cargo_address " . $where . " ORDER BY id DESC  ";
        $data =  $this->dbh->select($sql);

        return $data;
    }
    /**
     * 根据id获取地址详情
     * id: 权限id
     * @return 数组
     */
    public function getCargoAddressInfo($id = 0)
    {
        $sql = "SELECT  id,cid,uid,name,mobile,address,remark,type  FROM gl_cargo_address WHERE id=".$id;
        return $this->dbh->select_row($sql);
    }
    //添加货主地址
    public function addCargoAddress($params)
    {
        return $this->dbh->insert('gl_cargo_address',$params);
    }

    //修改货主地址
    public function updataCargoAddress($params,$id)
    {
        return $this->dbh->update('gl_cargo_address',$params,'id=' . intval($id));
    }

    //删除 供求
    public function deleteCargoAddress($id)
    {
        $data = [
            'is_del' => 1,
            'updated_at' => '=NOW()'
        ];
        return $this->dbh->update('gl_cargo_address',$data,'id=' . intval($id));
    }

}
