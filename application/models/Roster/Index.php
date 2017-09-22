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

    //2017-09-22 添加名单数据
    public function addRoster($params){

        #开启事物
        $this->dbh->begin();
        try{
            $where = " cid=".$params['cid']." AND type=".$params['type'];
            $res =  $this->dbh->delete('gl_blacklist',$where);
            if(empty($res)){
                $this->dbh->rollback();
                return false;
            }else{
                foreach ($params['info'] as $k=>$v){
                    $input = array(
                        'type'=>$params['type'],
                        'cid'=>$params['cid'],
                        'join_id'=>$v,
                        'is_del'=>0,
                        'created_ad' => '=NOW()',
                        'updated_at' => '=NOW()'
                    );

                    $data = $this->dbh->insert('gl_blacklist',$input);
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
