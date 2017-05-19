<?php

class WechatAction extends PublicAction
{

    protected $weObj;
    protected $wxconfig;

    function _initialize()
    {
        parent::_initialize();

        $data = M('WxConfig')->select();
        foreach ($data as $key=>$val) {
            $this->wxconfig[$val['varname']] = $val['value'];
        }

        import('@.ORG.Wechat' );
        $options = array(
                'token'=>$this->wxconfig['WEIXIN_TOKEN'], //填写你设定的key
                'encodingaeskey'=>$this->wxconfig['WEIXIN_ENCODINGAESKEY'], //填写加密用的EncodingAESKey，如接口为明文模式可忽略
                'appid'=>$this->wxconfig['WEIXIN_APPID'], //填写高级调用功能的app id
                'appsecret'=>$this->wxconfig['WEIXIN_APPSECRET'] //填写高级调用功能的密钥
            );

        $this->weObj = new Wechat($options);
    }

    function bind()
    {
        if (IS_POST) {
            $model = M('WxConfig');
            $msg = false;
            foreach($_POST as $key=>$value){
                $data['value']=$value;
                if($model->where("varname='".$key."'")->save($data)) {
                    $msg = true;
                }
            }

            //更新微信配置缓存
            $data = $model->select();
            $wxconfig = array();
            foreach ($data as $key=>$val) {
                $wxconfig[$val['varname']] = $val['value'];
            }
            F('Wxconfig', $wxconfig);

            if($msg){
                $this->success('提交成功!');
            }else{
                $this->error('没有发生更改!');
            }
            exit;
        }

        $this->assign($this->wxconfig);
        $this->display();
    }

    function welcome()
    {
        if (IS_POST) {
            $model = M('WxConfig');
            $msg = false;

            foreach ($_POST as $key => $value) {
                $data['value'] = $value;
                if ($model->where("varname='" . $key . "'")->save($data)) {
                    $msg = true;
                }
            }

            //更新微信配置缓存
            $data = $model->select();
            $wxconfig = array();

            foreach ($data as $key => $val) {
                $wxconfig[$val['varname']] = $val['value'];
            }
            F('Wxconfig', $wxconfig);

            if ($msg) {
                attach_update('wx');
                $this->success('提交成功!');
            } else {
                $this->error('没有发生更改!');
            }

        } else {
            $this->assign($this->wxconfig);
            attach_update_start();
            $this->display();
        }
    }

    function robot()
    {
        if (IS_POST) {
            $model = M('WxConfig');
            $msg = false;
            foreach($_POST as $key=>$value){
                $data['value']=$value;
                if($model->where("varname='".$key."'")->save($data)) {
                    $msg = true;
                }
            }

            //更新微信配置缓存
            $data = $model->select();
            $wxconfig = array();
            foreach ($data as $key=>$val) {
                $wxconfig[$val['varname']] = $val['value'];
            }
            F('Wxconfig', $wxconfig);

            if($msg){
                $this->success('提交成功!');
            }else{
                $this->error('没有发生更改!');
            }
            exit;
        } else {
            $this->assign($this->wxconfig);
            $this->display();
        }
    }

    function selfmenu()
    {
        $action = I('action','');

        $model = M('WxMenu');
        switch ($action) {
            case 'add':

                $ajaxMsg = array();

                if(!empty($_GET['clearTopMenu'])){
                    $model->where('id='.$_GET['pid'])->save(array('type'=>'','code'=>''));
                }

                if ($_GET['pid']==0) {
                    $topMenuNum = $model->where('pid=0')->count();
                    if($topMenuNum >= 3) {
                        $ajaxMsg['status'] = 1;
                        $ajaxMsg['info'] = '一级菜单不能超过3个';
                        $this->ajaxReturn($ajaxMsg);
                        exit;
                    }
                } else {
                    $topMenu = $model->find($_GET['pid']);
                    if(!empty($topMenu['type'])){
                        $ajaxMsg['status'] = 2;
                        $ajaxMsg['info'] = '使用二级菜单后，当前编辑的消息将会被清除。确定使用二级菜单！';
                        $this->ajaxReturn($ajaxMsg);
                        exit;
                    }
                    $this->assign('topMenu', $topMenu);
                }


                $this->assign($_GET);

                $this->display('addmenu');
                break;
            case 'insert':
                $data = array();
                $data['pid'] = I('pid');
                $data['name'] = I('name');
                $data['type'] = I('type');
                $data['code'] = I('code');

                if($model->add($data)){
                    $this->redirect('Wechat/selfmenu');
                }
                break;
            case 'sort':
                $ids = $_POST['listorders'];

                foreach($ids as $key=>$r) {
                    $data['listorder']=$r;
                    $model->where('id='.$key)->save($data);
                }

                $this->redirect('Wechat/selfmenu');
                break;
            case 'edit':
                $id = I('id',0);
                $wxMenu = $model->find($id);
                $wxTopMenu = $model->find($wxMenu['pid']);
                $sub_button = $model->where('pid='.$wxMenu['id'])->count();
                $wxMenu['sub_button'] = $sub_button;
                $this->assign('wxMenu', $wxMenu);
                $this->assign('wxTopMenu', $wxTopMenu);
                $this->display('editmenu');
                break;
            case 'update':
                if($model->save($_POST)){
                    $this->redirect('Wechat/selfmenu');
                }
                break;
            case 'delete':
                    $id = I('id');

                    if (isset($id)) {

                        if (false!==$model->delete($id)) {

                            $this->assign('jumpUrl', U('Wechat/selfmenu') );
                            $this->success(L('delete_ok'));
                        } else {
                            $this->error(L('delete_error').': '.$model->getDbError());
                        }
                    } else {
                        $this->error (L('do_empty'));
                    }
                break;
            default:
                $model = M('WxMenu');
                $menuList = $model->where('pid=0')->select();

                foreach($menuList as $key=>$val){
                    $data = $model->where('pid='.$val['id'])->select();
                    $menuList[$key]['sub_button'] = $data;
                }

                S('menuList',$menuList);

                $this->assign('menuList', $menuList);
                $this->display();
        }
    }


    function setMenu()
    {
        $menuList = array();
        $model = M('WxMenu');
        $data = $model->field('id,pid,name,type,code')->where('pid=0')->select();
        foreach ($data as $key => $value) {
            $sub_button = $model->field('name,type,code')->where('pid='.$value['id'])->select();
            if (empty($sub_button)) {
                $menuList['button'][$key]['type'] = $value['type'];
                $menuList['button'][$key]['name'] = $value['name'];
                if ($value['type'] == 'view') {
                    $menuList['button'][$key]['url'] = $value['code'];
                } else {
                    $menuList['button'][$key]['key'] = $value['code'];
                }
            } else {
                $menuList['button'][$key]['name'] = $value['name'];
                foreach ($sub_button as $k=>$v) {
                    $menuList['button'][$key]['sub_button'][$k]['type'] = $v['type'];
                    $menuList['button'][$key]['sub_button'][$k]['name'] = $v['name'];
                    if ($v['type'] == 'view') {
                        $menuList['button'][$key]['sub_button'][$k]['url'] = $v['code'];
                    } else {
                        $menuList['button'][$key]['sub_button'][$k]['key'] = $v['code'];
                    }
                }

            }
        }

        $result = $this->weObj->createMenu($menuList);
        if($result){
            $this->success('修改成功！');
        }
    }

    //关键词自动回复
    function smartreply()
    {
        $list = M('WxSmartreply')->select();

        $this->assign('list', $list);
        $this->display();
    }

    function smartreply_add()
    {
        $model = M('WxSmartreply');
        if(IS_POST){
            $data = array();
            $data['rulename'] = I('rulename');
            if (empty($data['rulename'])) {
                $this->error('规则名不能为空！');
            }
            $data['key'] = I('key');
            if (empty($data['key'])) {
                $this->error('关键字不能为空！');
            }
            $data['content'] = I('content');

            if($model->add($data)){
                $this->redirect('Wechat/smartreply');
            }
        }
        $this->display();
    }

    function smartreply_edit()
    {
        $model = M('WxSmartreply');
        if(IS_POST){
            $data = array();
            $data['id'] = I('id');
            $data['rulename'] = I('rulename');
            if (empty($data['rulename'])) {
                $this->error('规则名不能为空！');
            }
            $data['key'] = I('key');
            if (empty($data['key'])) {
                $this->error('关键字不能为空！');
            }
            $data['content'] = I('content');

            if($model->save($data)){
                $this->redirect('Wechat/smartreply');
            }
        }

        $id = $_GET['id'];
        $data = $model->find($id);
        $this->assign('smartreply', $data);
        $this->display();
    }


    function smartreply_del()
    {
        $model = M('WxSmartreply');
        $id = I('get.id');
        if (!$id) {
            $this->error('参数不正确！');
        }

        if(false !== $model->delete($id)){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败: '.$model->getDbError());
        }
    }


    function users()
    {
        //群发消息
        //获取粉丝列表
        $fansList = UserManage::getFansList();
        $groupList = UserManage::getGroupList();

        $userList = array();
        foreach ($fansList['data']['openid'] as $key => $value) {
            $userList[] = UserManage::getUserInfo($value);
        }

        $this->assign('userList', $userList);
        $this->assign('groupList', $groupList);
        $this->display();
    }


    /**
     * 发送图文消息
     *
     * @return void
     * @author
     **/
    function msg()
    {
        $list = M('weixin_msg')->select();
        $this->assign('list', $list);

        $this->display();
    }

    function msg_add()
    {

        $model = M('weixin_msg');
        if(IS_POST){

            if($model->add($_POST)){
                $this->redirect('Wechat/msg');
            }
        }
        $this->display();
    }

    function msg_send(){

        //获取粉丝列表
        $fansList = $this->weObj->getUserList();
        $list['touser'] = $fansList['data']['openid'];
        $list['mpnews'] = $news;
        $list['msgtype'] = 'mpnews';
        //上传图片
        $menuId = Media::upload(ROOT.'/Uploads/201505/55485aa8f26a7.jpg', 'image');

        // $menuId = Media::upload('http://www.grwy.cn/Uploads/201505/55485aa8f26a7.jpg', 'image');
        if (empty($menuId['media_id'])) {
            die('error');
        }
        //dump($menuId);exit;
        //上传图文消息
        $list = array();
        $list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'国人伟业', 'title'=>'测试标题1', 'content_source_url'=>'www.grwy.cn', 'digest'=>'测试摘要1', 'show_cover_pic'=>'1');
        $list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'国人伟业', 'title'=>'测试标题2', 'content_source_url'=>'www.grwy.cn', 'digest'=>'测试摘要2', 'show_cover_pic'=>'0');
        $list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'国人伟业', 'title'=>'测试标题3', 'content_source_url'=>'www.grwy.cn', 'digest'=>'测试摘要3', 'show_cover_pic'=>'0');
        $mediaId = AdvancedBroadcast::uploadNews($list);
        //给粉丝列表的用户群发图文消息
        $this->weObj->sendMassMessage($list);

    }
}