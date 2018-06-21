<?php
/**
 * [账户相关]
 * description
 * @Author daley
 * @Date   2017-03-07
 * @version 1.0
 */

class Capital_AccountController extends Rpc {
    public $m;
    /**
     * UserController::init()
     *
     * @return void
     */
    public function init() {
        $this->m = new Capital_AccountModel();
        parent::init();
        #   Yaf_Dispatcher::getInstance()->disableView();
    }

    /**
     * 获取公司开户信息
     * description
     * @Author Z
     * @Date   2017-03-07
     * @param  [type]     $company_id [description]
     * @return array
     */
    public function getAccountFunc($company_id){
        if(!is_numeric($company_id)){
            return array();
        }
        $company = $this->m->getCompanyAccount($company_id);
        return $company;
    }



    /**
     * 获取开户号
     * description
     * @Author Z
     * @Date   2017-03-07
     * @param  [type]     $company_id [description]
     * @return array
     */
    public function getBankaccountnoFunc($company_id){

        if(!is_numeric($company_id)){
            return 0;
        }
        //先找出公司是否有户头
        $company = $this->m->getCompanyAccount($company_id);

        if($company){
            $bankaccountno = $company['bankaccountno'];
        }else{
            $bankaccountno = 0;
        }

        return $bankaccountno;
    }


    /**
     * 获取公司开通资金账户申请信息
     * description
     * @Author Z
     * @Date   2017-03-08
     * @return array
     */
    public function getCapitalApplyFunc($company_id){
        if(!is_numeric($company_id)){
            return array();
        }
        $apply = $this->m->getCompanyApply($company_id);
        if(!$apply){
            $apply =  array(
                'id'                => '',
                'legalpersonname'   => '',
                'certno'            => '',
                'commaddress'       => '',
                'contactname'       => '',
                'contactphone'      => '',
                'mailaddress'       => '',
                'auditstatus'       => '',
            );
        }
        return $apply;
    }

    /**
     * 增加/编辑申请
     * description
     * @Author Z
     * @Date   2017-03-08
     */
    public function changeCapitalApplyFunc($data,$apply_id)
    {
        return $this->m->changeCompanyApply($data,$apply_id);
        # code...
    }

    /**
     * 新增提现申请
     * description
     * @Author Z
     * @Date   2017-03-09
     * @param  [type]     $data [description]
     */
    public function addWithdrawalsFunc($data)
    {
        $withdrawals = $this->m->addWithdrawalsFunc($data);
        if($withdrawals){
            return array('status' => 'success','msg'=>'新增成功','url'=>'');
        }else{
            return array('status' => 'failed','msg'=>'新增失败','url'=>'');
        }
        # code...
    }



}