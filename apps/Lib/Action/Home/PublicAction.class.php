<?php

class PublicAction extends BaseAction
{

    public function _initialize()
    {
        parent::_initialize();

        //301跳转
        $siteDomains = C('SITE_DOMAINS');
        if (!empty($siteDomains)) {
            $siteDomainArr = explode("\n", C('SITE_DOMAINS'));

            if (in_array($_SERVER['SERVER_NAME'], $siteDomainArr)) {
                header( "HTTP/1.1 301 Moved Permanently" );
                header("location: http://".C('SITE_DOMAIN'));
            }
        }

        //检测是否是手机访问
        if (C('SUB_DOMAIN')) {
            $this->checkMobile();
        }

        //获取碎片
        $data_block = M('Block')->where('`groupid`=1 and `lang`='.LANG_ID)->select();
        if (!empty($data_block)) {
            $block = array();
            foreach ($data_block as $val) {
                $block[$val['id']] = $val['content'];
            }

            $this->assign('block',$block);
        }
    }

    //检测是否登录
    function checkLogin()
    {
        if (empty($_SESSION['member']['username'])) {
            $this->redirect('/user/login');
        }
    }


    //检测是否是移动设备
    function checkMobile()
    {
        import('ORG.Util.MobileDetect');
        $detect = new MobileDetect;
        if ($detect->isMobile() || $detect->isTablet()) {
            redirect('http://'.C('SITE_WAP_DOMAIN'));
        }
    }
}