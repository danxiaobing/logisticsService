<?php

/**
 * 获取公司信息
 *
 * @author  Daley
 * @date    2016-08-08
 * @version $Id$
 */
class Roster_IndexModel
{
    public $dbh = null;

    /**
     * Constructor
     *
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mc = null)
    {
        $this->dbh = $dbh;
    }
    /**
     * 获取名单列表
     * @param string $fields
     * @param string $where
     * @return array
     */
    public function getList($fields=null,$where=null)
    {
        $sql = "SELECT $fields FROM `gl_blacklist` WHERE `is_del`= 0 ";
        if($where)$sql .= "AND $where";
        return $this->dbh->select($sql);
    }
    //添加名单
    public function addRoster($params)
    {
        return $this->dbh->insert('gl_blacklist',$params);
    }

    //删除名单
   /* public function deleteRoster($id)
    {
        $data = [
            'is_del' => 1,
            'updated_at' => '=NOW()'
        ];
        return $this->dbh->update('gl_blacklist',$data,'id=' . intval($id));
    }*/

    public function deleteRoster($where)
    {
        return $this->dbh->delete('gl_blacklist',$where);
    }




}
