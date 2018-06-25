<?php
/**
 * Created by PhpStorm.
 * User: ai
 * Date: 2018/6/25
 * Time: 09:32
 */
class App_Carrier_ImModel
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
     * 保存聊天信息
     * @param $data array
     * @return array or boolen
     */
    
    public function addChatInfo($data){  
        $this->dbh->begin();
        try{
            $info = [
                'chat_type'=>intval($data['chat_type']),
                'content'=>addslashes($data['content']),
                'original'=>addslashes($data['original']),
                'driver_mobile'=>addslashes($data['driver_mobile']),
                'scheduler_mobile'=>addslashes($data['scheduler_mobile']),
                'created_at'=>'=NOW()',
                'client_time'=>$data['time']?$data['time']:'=NOW()',
            ];

            $map_location_info = $this->dbh->insert('gl_im', $info);
            if(!$map_location_info){
                throw new Exception('聊天信息保存失败', 300);
                return false;
            }
            $this->dbh->commit();
            return true;
        }catch (Exception $e) {
            $this->dbh->rollback();
            return false;
            //return ['code' => $e->getCode(), 'msg'=>$e->getMessage()];
        }
    }
    
    
}
