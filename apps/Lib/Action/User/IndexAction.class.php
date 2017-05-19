<?php

class IndexAction extends PublicAction
{

    protected $userid;

    function _initialize()
    {
        parent::_initialize();
        //检测用户是否登陆
        $this->checkLogin();
        $this->userid = $_SESSION['member']['id'];
    }

    public function index()
    {
        $member_db = M('Member');
        $user = $member_db->find($this->userid);
        $this->assign('user',$user);

        $role = F('MemberGroup');
        $this->assign('role',$role);
        $this->display();
    }
}