<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/8/14
 * Time: 18:32
 */
class TransRange_TransModel
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


    //新增运力范围管理
    public function addTrans($input,$data){
        //事务
        $this->dbh->begin();
        try{
            //gl_companies_range 插入基本信息
            $id = $this->dbh->insert('gl_companies_range',$input);
            if(!$id){
                //回滚
               $this->dbh->rollback();
               return false;
            }

            //gl_companies_range_region  插入关联信息
            //方案一
    
            $data['r_id'] = $id;
            $res =  $this->dbh->insert('gl_companies_range_region_bak',$data);

            if($res){
                $this->dbh->commit();
                return true;
            }else{
                $this->dbh->rollback();
                return false;
            }

        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
        }
    }


}
