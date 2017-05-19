<?php

class AccountAction extends PublicAction
{

    public function register()
    {
        if (IS_POST) {
            $username   = trim($_POST['username']);
            $email      = trim($_POST['email']);

            if (md5($_POST['verifyCode']) != $_SESSION['verify']) {
                $this->error('验证码错误！');
            }

            if(!empty($_POST['password']) && $_POST['repassword'] != $_POST['password']) {
                $this->error('两次密码输入不一致');
            }

            //注册用户初始状态
            $status = $this->config['member_registecheck'] ? 0 : 1;

            $_POST['login_count'] = 1;
            $_POST['last_logintime'] = time();
            $_POST['status'] = $status;
            $_POST['groupid'] = 1;

            $model = D('Member');

            if ($data = $model->create()) {
                if (false !== $model->add()) {
                    $uid = $model->getLastInsID();

                    if($this->config['member_emailcheck']){
                        $yzh_auth = authcode($uid."-".$username."-".$email, 'ENCODE', $this->SysConfig['ADMIN_ACCESS'], 3600*24*3);//3天有效期
                        $url = 'http://'.$_SERVER['HTTP_HOST'].U('Account/regcheckemail').'?code='.$yzh_auth;

                        $click = "<a href=\"$url\" target=\"_blank\">点击这里</a>";

                        $message = str_replace(array('{click}','{url}','{sitename}'),array($click,$url,$this->config['site_name']),$this->config['member_emailchecktpl']);

                        $r = sendmail($email,'注册认证邮件-'.$this->SysConfig['site_name'], $message, $this->SysConfig);
                        if ($r) {
                            $this->assign('send_ok',1);
                            $this->assign('username',$username);
                            $this->assign('email',$email);
                            $this->display('emailcheck');
                            exit;
                        }
                    }

                    $this->success('注册成功！', U('Account/login'));
                } else {
                    $this->error('注册失败！');
                }
            } else {
                $this->error($model->getError());
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

    function checkEmail()
    {
        $email = $_GET['email'];
        $userid = intval($_GET['userid']);
        if(empty($userid)){
            if ($this->db->getByEmail($email)) {
                echo 'false';
            } else {
                echo 'true';
            }
        } else {
            //判断邮箱是否已经使用
            if ($this->db->where("id!={$userid} and email='{$email}'")->find()) {
                echo 'false';
            } else {
                echo 'true';
            }
        }
    }

    function checkUsername()
    {
        $username = $_GET['username'];
        if ($this->db->getByUsername($username)) {
          echo 'false';
        } else {
          echo 'true';
        }
        exit;
    }

    function login()
    {
        if (IS_POST) {
            $model = M('Member');

            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $verifyCode = trim($_POST['verifyCode']);

            if (empty($username) || empty($password)) {
                $this->error('用户名和密码不能为空！');
            }

            if ($this->config['member_login_verify'] && md5($verifyCode) != $_SESSION['verify']) {
                $this->error('验证码错误！');
            }

            $authInfo = $model->getByUsername($username);

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
                $model->save($data);

                $key = sysmd5($this->SysConfig['ADMIN_ACCESS'].$_SERVER['HTTP_USER_AGENT']);
                $auth = authcode($authInfo['id']."-".$authInfo['groupid']."-".$authInfo['password'], 'ENCODE', $key);

                $_SESSION['member'] = $authInfo;
                $_SESSION['member']['auth'] = $auth;

                $this->success('登陆成功！',U('Index/index'));
            }
        } else {
            if (!empty($_SESSION['member']['username'])) {
                redirect('Index/index');
            }

            $this->display();

        }
    }

    function getpass()
    {
        $this->display();
    }

    function repassword()
    {
        if($_POST['dosubmit']){
            $verifyCode = trim($_POST['verify']);
            if(md5($verifyCode) != $_SESSION['verify']){
                $this->error(L('error_verify'));
            }
            if(trim($_POST['repassword'])!=trim($_POST['password'])){
                $this->error(L('password_repassword'));
            }

            list($userid,$username, $email) = explode("-", authcode($_POST['code'], 'DECODE', $this->SysConfig['ADMIN_ACCESS']));

            $model = M('Member');
            //判断邮箱是用户是否正确
            $data = $model->where("id={$userid} and username='{$username}' and email='{$email}'")->find();
            if($data){
                $model->password = sysmd5(trim($_POST['password']));
                $model->updatetime = time();
                $model->last_ip = get_client_ip();
                $model->save();
                $this->assign('jumpUrl',U('login/index'));
                $this->assign('waitSecond',3);
                $this->success(L('do_repassword_success'));
            }else{
                $this->error(L('check_url_error'));
            }
        }

        $code = str_replace(' ','+',$_REQUEST['code']);
        $this->assign('code',$code);
        $this->display();
    }

    function sendmail()
    {
        $verifyCode = trim($_POST['verifyCode']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if(empty($username) || empty($email)){
            $this->error(L('empty_username_empty_password'));
        }elseif(md5($verifyCode) != $_SESSION['verify']){
            $this->error(L('error_verify'));
        }

        $user = M('Member');
        //判断邮箱是用户是否正确
        $data = $user->where("username='{$username}' and email='{$email}'")->find();
        if($data){
            $yzh_auth = authcode($data['id']."-".$data['username']."-".$data['email'], 'ENCODE',$this->config['ADMIN_ACCESS'],3600*24*3);//3天有效期
            $username = $data['username'];
            $url =  'http://'.$_SERVER['HTTP_HOST'].U('Login/repassword?code='.$yzh_auth);
            $message = str_replace(array('{username}','{url}','{sitename}'),array($username,$url,$this->config['site_name']),$this->config['member_getpwdemaitpl']);

            $r = sendmail($email,'密码找回-'.$this->SysConfig['site_name'],$message,$this->SysConfig);
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

        if($_REQUEST['resend']){
          $uid = $this->_userid;
          $username = $this->_username;
          $email = $this->_email;

          if($this->config['member_emailcheck']){
            $yzh_auth = authcode($uid."-".$username."-".$email, 'ENCODE',$this->SysConfig['ADMIN_ACCESS'],3600*24*3);//3天有效期
            $url = 'http://'.$_SERVER['HTTP_HOST'].U('Account/regcheckemail?code='.$yzh_auth);
            $click = "<a href=\"$url\" target=\"_blank\">".L('CLICK_THIS')."</a>";
            $message = str_replace(array('{click}','{url}','{sitename}'),array($click,$url,$this->SysConfig['site_name']),$this->config['member_emailchecktpl']);

            $r = sendmail($email,'注册认证邮件-'.$this->SysConfig['site_name'],$message,$this->SysConfig);
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

        list($userid, $username, $email) = explode("-", authcode($code, 'DECODE', $this->SysConfig['ADMIN_ACCESS']));

        $model = M('Member');
        //判断邮箱是用户是否正确
        $data = $model->where("id={$userid} and username='{$username}' and email='{$email}'")->find();
        if ($data) {
            $model->groupid = 2;
            $model->id = $userid;
            $model->save();
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
        $this->redirect('Account/login');
    }
}