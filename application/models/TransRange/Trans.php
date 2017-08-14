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
            $ids = [];
            foreach ($data as $k => $val) {
                $arr = explode(',', $val);
                $input1['r_id'] = $id;
                $input1['province_id'] = $arr[0];
                $input1['city_id'] = $arr[1];
                $input1['area_id'] = $arr[2];
                $input1['created_at'] = '=NOW()';
                $input1['updated_at'] = '=NOW()';
                $ids[] = $this->dbh->insert('gl_companies_range_region',$input1);

            }
            if(!empty($ids)){
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
