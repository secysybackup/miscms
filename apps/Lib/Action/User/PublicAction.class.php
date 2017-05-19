<?php

class PublicAction extends BaseAction
{
    protected $MemberConfig;

    public function _initialize()
    {
        parent::_initialize();

        if ( ! F($this->MemberConfig)) {
            $list = M('MemberConfig')->select();

            $this->MemberConfig = array();

            foreach ($list as $key=>$r) {
                $this->MemberConfig[$r['varname']]=$r['value'];
            }

            F('MemberConfig',$this->MemberConfig);
        } else {
            $this->MemberConfig = F($this->MemberConfig);
        }
    }

    //检测是否登录
    function checkLogin()
    {
        if (empty($_SESSION['member']['username'])) {
            $this->redirect('/user/login');
        }
    }
}