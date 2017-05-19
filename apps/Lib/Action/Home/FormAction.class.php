<?php

class FormAction extends PublicAction
{

    protected $Form;

    public function _initialize()
    {
        parent::_initialize();

        $this->Form = getCache('Form');
    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     **/
    public function insert()
    {
        $formid = I('post.formid', 0 ,'intval');

        if (!$formid) {
            $this->error('缺少必要的参数！');
        }

        $tablename = $this->Form[$formid]['tablename'];

        $form_db = M($tablename);

        //确认表单数据未满10000条
        $count = $form_db->count('id');
        if ($count>=10000) {
            exit;
        }

        $fields = getCache('FormField_'.$formid);

        foreach($fields as $key => $res){
            $res['setup'] = json_decode($res['setup'],true);
            $fields[$key] = $res;
        }

        $_POST['ip'] = get_client_ip();

        //验证码检测
        if($this->Form[$formid]['captcha'] && (md5($_POST['verifyCode']) != $_SESSION['verify'])){
            $this->error('验证码错误！');
        }

        $post = $_POST;
        import('ORG.Util.Verify');
        $verify = new Verify();
        $msg = '';
        foreach ($fields as $key => $val) {
            if ($val['required'] && empty($post[$val['field']])) {
                $this->error($val['name'].'不能为空！');
                break;
            }

            switch($val['pattern']){
                case 'email': //电子邮件地址
                    if (!$verify->isEmail($post[$val['field']])) {
                        $this->error($val['name'].'格式不正确！');
                    }
                    break;
                case 'url': //网址
                    if (!$verify->isUrl($post[$val['field']])) {
                        $this->error($val['name'].'格式不正确！');
                    }
                    break;
                case 'mobile'://手机号码
                    if (!$verify->isMobile($post[$val['field']])) {
                        $this->error($val['name'].'格式不正确！');
                    }
                    break;
            }

            $msg .= $val['name'].':'.$post[$val['field']].'<br/>';
        }

        $post['createtime'] = time();

        if(!empty($_SESSION['member'])){
            $post['userid'] = $_SESSION['member']['id'];
            $post['username'] = $_SESSION['member']['username'];
        }

        $id = $form_db->add($post);

        if ($id !==false) {

            //是否发送邮件
            if ($this->Form[$formid]['sendmail']) {
                $email = C('mail_accept');
                sendmail($email,$this->Config['site_name'],$msg,$this->SysConfig);
            }
            $this->success('提交成功！');
        } else {
            $this->error(L('add_error').': '.$form_db->getDbError());
        }
    }

    public function job()
    {
        $model = D('Resume');

        //确认表单数据未满1000条
        $count = $model->count('id');
        if ($count>=1000) {
            exit;
        }

        //验证码检测
        if($_POST['verify_status']){
            if((md5($_POST['verifyCode']) != $_SESSION['verify'])){
                $this->assign('script','javascript:history.go(-1);');
                $this->error(L('ERROR_VERIFY'));
            }
        }

        $msg = '';
        foreach ($_POST as $key => $val) {
            $msg .= $val['name'].':'.$_POST[$val['field']].'<br/>';
        }


        if (false === $model->create()) {
            $this->error($model->getError());
        }

        $id = $model->add();

        if ($id !==false) {

            //是否发送到邮箱
            //$email = C('mail_accept');
            //sendmail($email,$this->Config['site_name'],$msg,$this->SysConfig);

            $this->success('提交成功！');
        } else {
            $this->error(L('add_error').': '.$model->getDbError());
        }
    }
}