<?php

class MyAction extends PublicAction
{

    protected $userid;

    function _initialize()
    {
        parent::_initialize();
        //检测用户是否登陆
        $this->checkLogin();
        $this->userid = $_SESSION['member']['id'];
        $member_db = M('Member');
        $user = $member_db->find($this->userid);
        $this->assign('user',$user);
    }

    public function baseinfo()
    {
        if (IS_POST) {
            $member_db = M('Member');
            $_POST['id'] = $this->userid;
            if (!$member_db->create($_POST)) {
                $this->error($member_db->getError());
            }
            $member_db->update_time = time();
            $member_db->last_ip = get_client_ip();
            $result = $member_db->save();
            if(false !== $result) {
                $this->success('保存成功！');
            }else{
                $this->error('保存失败！');
            }
            exit;
        }
        $this->display();
    }

    public function avatar()
    {
        $member_db = M('Member');

        if (IS_POST) {
            $_POST['id'] = $this->userid;

            if(!empty($_FILES['pic']['name'])){
                import('ORG.Net.UploadFile');
                $upload = new UploadFile();// 实例化上传类
                $upload->maxSize  = 3145728 ;// 设置附件上传大小
                $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->savePath =  UPLOAD_PATH;// 设置附件上传目录
                $upload->thumb = true;
                $upload->thumbMaxWidth = '204';
                $upload->thumbMaxHeight = '298';
                if (!$upload->upload()) {// 上传错误提示错误信息
                    $this->error($upload->getErrorMsg());
                } else {// 上传成功 获取上传文件信息
                    $info = $upload->getUploadFileInfo();
                    $_POST['image'] = $info[0]['savename'];
                    $_POST['avatar'] = 'thumb_'.$info[0]['savename'];
                }
            }

            $member_db->update_time = time();
            $member_db->avatar = $_POST['avatar'];
            $member_db->last_ip = get_client_ip();
            $result = $member_db->save();

            if(false !== $result) {
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
            exit;
        }

        $this->display();
    }

    public function updatepwd()
    {
        if(IS_POST){
            $member_db = M('Member');
            if($_POST['password'] != $_POST['repassword']){
                $this->error('两次密码不一致！');
            }

            $map = array();
            $map['password']= sysmd5($_POST['oldpassword']);
            $map['id'] = $this->userid;
            $map['username'] = $_SESSION['member']['username'];

            //检查用户
            if(!$member_db->where($map)->field('id')->find()) {
                $this->error('旧密码错误！');
            }else {
                $member_db->email = $_POST['email'];
                $member_db->id = $this->userid;
                $member_db->update_time = time();
                $member_db->password = sysmd5($_POST['password']);
                $r = $member_db->save();
                $this->assign('jumpUrl',U('Account/password'));
                if($r){
                    $this->success('修改成功！');
                }else{
                    $this->error('修改失败！');
                }
            }
            exit;
        }
        $this->display();
    }

    function address()
    {
        $address_db = M('MemberAddress');
        if (IS_POST) {
            $_POST['id'] = $this->userid;
            if (!$address_db->create($_POST)) {
                $this->error($address_db->getError());
            }
            $result = $address_db->save();
            if(false !== $result) {
                $this->success('保存成功！');
            }else{
                $this->error('保存失败！');
            }
            exit;
        }

        $list =  $address_db->where('userid='.$this->userid)->select();
        $this->assign('list', $list);
        $this->display();
    }

    //上传头像
    public function uploadImg()
    {
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();                     // 实例化上传类
        $upload->maxSize = 1*1024*1024;                 //设置上传图片的大小
        $upload->allowExts = array('jpg','png','gif');  //设置上传图片的后缀
        $upload->uploadReplace = true;                  //同名则替换

        //完整的头像路径
        $path = UPLOAD_PATH.'face/';
        $upload->savePath = $path;
        if(!$upload->upload()) {                        // 上传错误提示错误信息
            $this->ajaxReturn('',$upload->getErrorMsg(),0,'json');
        }else{                                          // 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            $_POST['image'] = $info[0]['savename'];
            $temp_size = getimagesize($path.$_POST['image']);
            if($temp_size[0] < 100 || $temp_size[1] < 100){//判断宽和高是否符合头像要求
                $this->ajaxReturn(0,'图片宽或高不得小于100px！',0,'json');
            }
            $this->ajaxReturn('/uploads/face/'.$_POST['image'],$info,1,'json');
        }
    }


    //裁剪并保存用户头像
    public function cropImg()
    {
        $member_db = M('Member');
        //图片裁剪数据
        $params = $this->_post();                       //裁剪参数
        if(!isset($params) && empty($params)){
            return;
        }

        //要保存的图片
        $pic_path = $real_path = '.'.$params['src'];

        import('ORG.Util.Image.ThinkImage');
        $Think_img = new ThinkImage(THINKIMAGE_GD);
        //裁剪原图
        $Think_img->open($pic_path)->crop($params['w'],$params['h'],$params['x'],$params['y'])->save($real_path);
        //生成缩略图
        $Think_img->open($real_path)->thumb(100,100, 1)->save($real_path);

        $member_db->update_time = time();
        $member_db->avatar = $real_path;
        $member_db->last_ip = get_client_ip();
        $result = $member_db->save();

        $_SESSION['member']['avatar'] = $real_path;
        $this->success('上传头像成功');
    }


    function safetyCenter()
    {
        $this->display();
    }

}