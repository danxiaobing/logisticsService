<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/16
 * Time: 14:13
 */
class Basicdata_FeedbackController extends Rpc
{

    public function init()
    {
        parent::init();

    }

    //获取所有
    public function getFeedbackFunc($search){
        $C = new Basicdata_FeedbackModel(Yaf_Registry::get("db"));
        return $C->getFeedback($search);
    }

    //查看上传图片
    public function getFileFunc($id){
        $P = new Basicdata_FeedbackModel(Yaf_Registry::get("db"));
        return $P->getFile($id);
    }
}