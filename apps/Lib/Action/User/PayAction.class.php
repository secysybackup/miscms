<?php

class PayAction extends PublicAction
{
    protected $userid;

    function _initialize()
    {
        parent::_initialize();
        $this->checkLogin();
        $this->db = M('Order');
        $this->userid = $_SESSION['member']['id'];
        $user =  M('Member')->find($this->userid);
        $this->assign('vo',$user);
    }


    public function respond()
    {
        $member_db = M('Member');

        $user = $member_db->find($this->userid);
        $this->assign('vo',$user);
        $pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
        $pay_code = ucfirst($pay_code);
        $Payment = M('Payment')->getByPayCode($pay_code);

        if(empty($Payment)) {
            $this->error(L('PAY CODE EROOR!'));
        }

        $aliapy_config = unserialize($Payment['pay_config']);
        import("@.Pay.".$pay_code);
        $pay = new $pay_code($aliapy_config);
        $r = $pay->respond();
        $this->assign('jumpUrl',U('order/index'));

        if($r){
            $this->error('支付成功！');
        }else{
            $this->error('支付失败！');
        }
    }
}