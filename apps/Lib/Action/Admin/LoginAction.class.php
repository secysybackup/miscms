<?php

class LoginAction extends Action
{

    public function index()
    {
        if(!empty($_SESSION['admin'])){
            $this->redirect('Index/index');
        }
        $this->display();
    }

    public function doLogin()
    {
        $user = M('User');

        if(C('TOKEN_ON') && !$user->autoCheckToken($_POST)){
            //$this->error('表单令牌错误');
        }

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $verifyCode = trim($_POST['verifyCode']);

        if (empty($username) || empty($password)) {
            $this->error('账号错误，请输入正确的用户名和密码！');
        } elseif (md5($verifyCode) != $_SESSION['verify']){
            $this->error('验证码错误，请重新输入！');
        }

        $condition = array();
        $condition['username'] = array('eq',$username);
        import('ORG.Util.RBAC');

        $authInfo = RBAC::authenticate($condition,'User');
        //使用用户名、密码和状态的方式进行认证
        if (false === $authInfo) {
            $this->error(L('empty_userid'));
        } else {
            if($authInfo['password'] != sysmd5($_POST['password'])) {
                $this->error('登录失败，请重新登录。');
            }

            $_SESSION['admin']['id'] = $authInfo['id'];
            $_SESSION['admin']['username'] = $authInfo['username'];
            $_SESSION['admin']['role'] = $authInfo['role'];
            $_SESSION[C('USER_AUTH_KEY')] = $authInfo['id'];

            RBAC::saveAccessList();
            if ($authInfo['role']==1) {
                $_SESSION[C('ADMIN_AUTH_KEY')] = true;
            }

            //记录行为
            action_log('user_login', 'user', $authInfo['id'], $authInfo['id']);

            //保存登录信息
            $data = array();
            $data['id']  = $authInfo['id'];
            $data['last_login_time']  = time();
            $data['last_ip'] = get_client_ip();
            $data['login_count'] = array('exp','login_count+1');
            $user->save($data);

            if ($_POST['ajax']) {
                $authInfo['info'] = '登陆成功';
                $this->ajaxReturn($authInfo);
            } else {
                $this->assign('jumpUrl',U('index/index'));
                $this->success('登陆成功');
            }
        }
    }

    function lostpassword()
    {
        if(IS_POST){
            $email = I('post.user_login','');
            if (empty($email)) {
                $this->error('请输入邮箱地址');
            }

            $r = M('User')->where(array('email'=>$email))->find();

            if ($r){
                $config = getCache('Config_'.C('DEFAULT_LANG'));

                $auth = authcode($r['id']."-".$r['username']."-".$r['email'], 'ENCODE',C('ADMIN_ACCESS'),3600*24);//24小时有效期
                $url = 'http://'.$_SERVER['HTTP_HOST'].U('Admin/Login/repassword?code='.$auth);

                $message = "尊敬的用户{$r['username']}，您好！<br><br><br>您可以通过点击以下链接重置帐户密码:<br><br><a href=\"{$url}\">{$url}</a><br><br>为保障您的帐号安全，请在24小时内点击该链接，您也可以将链接复制到浏览器地址栏访问。 若如果您并未尝试修改密码，请忽略本邮件，由此给您带来的不便请谅解。<br><br><br>本邮件由系统自动发出，请勿直接回复！";
                $r = sendmail($email,'找回密码-'.$config['site_name'],$message);

                if($r){
                    $this->success('邮件发送成功！');
                } else {
                    $this->error('邮件发送失败！');
                }
            } else {
                $this->error('邮箱不存在！');
            }
            exit;
        }

        $this->display();
    }

    /**
     * 退出登录
     *
     */
    public function logout()
    {
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {

          unset($_SESSION[C('USER_AUTH_KEY')]);
          unset($_SESSION);
          session_destroy();
          $this->success('安全退出');
        }else {
          $this->assign('jumpUrl',U('login/index'));
          $this->error(L('logined'));
        }
    }

    function repassword()
    {
        if (IS_POST) {
            $verifyCode = trim($_POST['verify']);
            if(md5($verifyCode) != $_SESSION['verify']){
                $this->error(L('error_verify'));
            }
            if(trim($_POST['repassword'])!=trim($_POST['password'])){
                $this->error(L('password_repassword'));
            }
            list($userid, $username, $email) = explode("-", authcode($_POST['code'], 'DECODE', C('ADMIN_ACCESS')));

            $user_db = M('User');
            //判断邮箱是用户是否正确
            $data = $user_db->where("id={$userid} and username='{$username}' and email='{$email}'")->find();
            if ($data) {
                $user_db->password = sysmd5(trim($_POST['password']));
                $user_db->updatetime = time();
                $user_db->last_ip = get_client_ip();
                $user_db->save();
                $this->success('重置密码成功！正在跳转到登陆页面.', U('Login/index'));
            }
            exit;
        }

        $code = I('get.code','');
        $this->assign('code',$code);
        $this->display();
    }

    public function verify()
    {
        ob_clean();
        header("Content-type: image/png");
        import("ORG.Util.Image");
        Image::buildImageVerify(4,1,'png',50,25);
    }

}