<?php

class AccountAction extends PublicAction
{


    //登陆
    function login()
    {
        if (IS_POST) {
            $member_db = M('Member');

            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $verifyCode = trim($_POST['verifyCode']);

            if (empty($username) || empty($password)) {
                $this->error('用户名和密码不能为空！');
            }

            if ($this->MemberConfig['login_verify'] && md5($verifyCode) != $_SESSION['verify']) {
                $this->error('验证码错误！');
            }

            $authInfo = $member_db->getByUsername($username);

            //使用用户名、密码和状态的方式进行认证
            if (empty($authInfo)) {
                $this->error('帐号不存在或已禁用！');
            } else {

                if($authInfo['password'] != sysmd5($_POST['password'])) {
                    $this->error('密码错误');
                }

                //保存登录信息
                $data = array();
                $data['id'] = $authInfo['id'];
                $data['last_logintime']  =  time();
                $data['last_ip']  =   get_client_ip();
                $data['login_count']  =  array('exp','login_count+1');
                $member_db->save($data);

                $key = sysmd5(C('ADMIN_ACCESS').$_SERVER['HTTP_USER_AGENT']);
                $auth = authcode($authInfo['id']."-".$authInfo['groupid']."-".$authInfo['password'], 'ENCODE', $key);

                $_SESSION['member'] = $authInfo;
                $_SESSION['member']['auth'] = $auth;

                $this->success('登陆成功！',U('index/index'));
            }
        } else {
            if (!empty($_SESSION['member']['username'])) {
                redirect('/user/Index/index');
            }

            $this->display();
        }
    }

    //注册
    public function register()
    {
        if (IS_POST) {

            //注册用户初始状态
            $status = $this->MemberConfig['registecheck'] ? 0 : 1;

            $_POST['login_count'] = 1;
            $_POST['last_logintime'] = time();
            $_POST['status'] = $status;
            $_POST['groupid'] = 1;

            $member_db = D('Member');

            if ($data = $member_db->create()) {
                if (md5($_POST['verifyCode']) != $_SESSION['verify']) {
                    $this->error('验证码错误！');
                }

                if(!empty($_POST['password']) && $_POST['repassword'] != $_POST['password']) {
                    $this->error('两次密码输入不一致');
                }

                if (false !== $member_db->add()) {
                    $uid = $member_db->getLastInsID();

                    if($this->MemberConfig['emailcheck']){
                        $this->success('注册成功！', U('Login/emailcheck'));
                    }
                    $this->success('注册成功！', '/user/login');
                } else {
                    $this->error('注册失败！');
                }
            } else {
                $this->error($member_db->getError());
            }

        } else {
            if(!empty($_SESSION['member'])){
                $this->assign('forward','');
                $this->assign('jumpUrl','/');
                $this->success(L('login_ok'));
            }
            $this->display();
        }
    }

    //判断邮箱是否存在
    function checkEmail()
    {
        $member_db = D('Member');
        $email = $_GET['email'];
        $userid = intval($_GET['userid']);
        if(empty($userid)){
            if ($member_db->getByEmail($email)) {
                echo 'false';
            } else {
                echo 'true';
            }
        } else {
            //判断邮箱是否已经使用
            if ($member_db->where("id!={$userid} and email='{$email}'")->find()) {
                echo 'false';
            } else {
                echo 'true';
            }
        }
    }

    //检测用户名是否存在
    function checkUsername()
    {
        $member_db = D('Member');
        $username = $_GET['username'];
        if ($member_db->getByUsername($username)) {
          echo 'false';
        } else {
          echo 'true';
        }
        exit;
    }

    //找回密码
    function forgetpwd()
    {
        $this->display();
    }

    //重置密码
    function repassword()
    {
        list($userid,$username, $email) = explode("-", authcode($_POST['code'], 'DECODE', C('ADMIN_ACCESS')));
        $member_db = M('Member');
        $data = $member_db->where("id={$userid} and username='{$username}' and email='{$email}'")->find();
        if($data){
            if($_POST['dosubmit']){
                $verifyCode = trim($_POST['verify']);
                if(md5($verifyCode) != $_SESSION['verify']){
                    $this->error(L('error_verify'));
                }
                if(trim($_POST['repassword'])!=trim($_POST['password'])){
                    $this->error('两次密码不一致');
                }

                $member_db->password = sysmd5(trim($_POST['password']));
                $member_db->updatetime = time();
                $member_db->last_ip = get_client_ip();
                $member_db->save();
                $this->assign('jumpUrl',U('login/index'));
                $this->assign('waitSecond',3);
                $this->success('重置密码成功！正在跳转到登陆页面.');
            } else {
                $this->display();
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            $this->display('./public/404.html');
        }
    }

    //发送邮件
    function sendmail()
    {
        $verifyCode = trim($_POST['verifyCode']);
        $email = trim($_POST['email']);

        if(empty($email)){
            $this->error('邮箱不能为空！');
        }elseif(md5($verifyCode) != $_SESSION['verify']){
            $this->error('验证码错误！');
        }

        $member_db = M('Member');
        //判断邮箱是用户是否正确
        $data = $member_db->where("email='{$email}'")->find();
        if($data){
            $auth = authcode($data['id']."-".$data['username']."-".$data['email'], 'ENCODE',C('ADMIN_ACCESS'),3600*24*3);//3天有效期
            $username = $data['username'];
            $url =  'http://'.$_SERVER['HTTP_HOST'].'/user/login/repassword?code='.$auth;
            $message = str_replace(array('{username}','{url}','{sitename}'),array($username,$url,$this->Config['site_name']),$this->MemberConfig['getpwdemaitpl']);

            $r = sendmail($email,'['.$this->Config['site_name'].']找回您的帐号密码',$message,$this->SysConfig);
            if ($r) {
                $returndata['username'] = $data['username'];
                $returndata['email'] = $data['email'];
                $this->ajaxReturn($returndata,L('USER_EMAIL_ERROR'),1);
            } else {
                $this->ajaxReturn(0,L('SENDMAIL_ERROR'),0);
            }
        } else {
            $this->ajaxReturn(0,L('USER_EMAIL_ERROR'),0);
        }

    }

    function emailcheck()
    {
        if(!$this->_userid && !$this->_username && !$this->_groupid && !$this->_email){
            $this->assign('forward','');
            $this->assign('jumpUrl',U('Login/index'));
            $this->success(L('noogin'));
        }

        if ($_REQUEST['resend']) {
            $uid = $this->_userid;
            $username = $this->_username;
            $email = $this->_email;

            if ($this->MemberConfig['emailcheck']) {
                $yzh_auth = authcode($uid."-".$username."-".$email, 'ENCODE',C('ADMIN_ACCESS'),3600*24*3);//3天有效期
                $url = 'http://'.$_SERVER['HTTP_HOST'].U('Account/regcheckemail?code='.$yzh_auth);
                $click = "<a href=\"$url\" target=\"_blank\">".L('CLICK_THIS')."</a>";
                $message = str_replace(array('{click}','{url}','{sitename}'),array($click,$url,$this->Config['site_name']),$this->MemberConfig['emailchecktpl']);

                $r = sendmail($email,'注册认证邮件-'.$this->Config['site_name'],$message,$this->SysConfig);
                if($r){
                  $this->assign('send_ok',1);
                  $this->assign('username',$username);
                  $this->assign('email',$email);
                  $this->display();
                }

                exit;
            }
        }
        if($this->_groupid==5){
            $this->display();
        }else{
            $this->error(L('do_empty'));
        }
    }

    function regcheckemail()
    {
        $code = str_replace(' ','+',$_GET['code']);

        list($userid, $username, $email) = explode("-", authcode($code, 'DECODE', C('ADMIN_ACCESS')));

        $member_db = M('Member');
        //判断邮箱是用户是否正确
        $data = $member_db->where("id={$userid} and username='{$username}' and email='{$email}'")->find();
        if ($data) {
            $member_db->groupid = 2;
            $member_db->id = $userid;
            $member_db->save();
            $this->assign('jumpUrl', U('Account/login'));
            $this->assign('waitSecond', 3);
            $this->success('邮箱认证成功！您已成为正式注册会员');
        } else {
            $this->error('链接已失效');
        }
    }

    function logout()
    {
        $_SESSION['member'] = '';
        $this->redirect('Login/index');
    }
}