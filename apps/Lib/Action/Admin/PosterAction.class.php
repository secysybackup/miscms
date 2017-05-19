<?php

class PosterAction extends PublicAction
{
    public function index()
    {
        $list = M('Poster')->select();
        $this->assign('list', $list);
        $this->assign($this->SysConfig);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    public function add()
    {
        $poster['appname'] = '空模板';
        $poster['music'] = '';
        $poster['sharetitle'] = '空模板标题';
        $poster['sharedesc'] = '空模板描述';
        $poster['createtime'] = time();
        $data = array(
                    0 => array(
                        'id'=>'',
                        'content'=>''
                    )
                );
        $poster['data'] = json_encode($data);

        $poster['id'] = M('Poster')->add($poster);
        $this->assign('poster', $poster);

        $this->display('edit');
    }

    public function edit()
    {
        if(IS_POST){
            $model = D('Poster');
            $_POST['updatetime'] = time();

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            if (false !== $model->save()) {
                $this->html($_POST['id']);
                $this->success('修改成功！',U('Poster/index'));
                exit;
            }
        } else {
            $id = I('get.id');
            $poster = M('Poster')->find($id);

            $this->assign('poster', $poster);

            $this->display();
        }
    }

    public function update()
    {
        $model = M('Poster');
        $data['id'] = $_POST['id'];
        $data['updatetime'] = time();
        if (!empty($_POST['data'])){
            $data['data'] = json_encode($_POST['data']);
        }
        if (!empty($_POST['appname'])){
            $data['appname'] = $_POST['appname'];
        }
        if (!empty($_POST['music'])){
            $data['music'] = $_POST['music'];
        }

        $model->save($data);
    }

    function delete()
    {
        $model = M('Poster');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false!==$model->delete($id)){
                $file = './poster/' . $id . '.html';
                if(is_file($file)) unlink($file);
                $dir = './uploads/poster/' . $id;
                if(is_dir($dir)) dir_delete($dir);
                $this->success('删除成功');
            }else{
                $this->error('删除失败: '.$model->getDbError());
            }
        }else{
            $this->error('请选择需要删除的信息！');
        }
    }

    function message()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('缺少参数！');
        }
        $model = M("PosterMessage");

        $map['poster_id'] = $id;

        //取得满足条件的记录总数
        $count = $model->where($map)->count('id');

        if ($count > 0) {
            import("@.ORG.Page");

            $p = new Page($count, 15);

            //分页查询数据
            $voList = $model->where($map)->limit($p->firstRow . ',' . $p->listRows)->order('id desc')->select ( );

            //分页跳转的时候保证查询条件
            foreach ( $map as $key => $val ) {
                if (! is_array ( $val )) {
                    $p->parameter .= "$key=" . urlencode ( $val ) . "&";
                }
            }
            $map[C('VAR_PAGE')]='{$page}';

            $p->urlrule = U('poster/message', $map);
            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $voList );
            $this->assign('page', $page );
        }

        $this->display();
    }

    function deletemsg()
    {
        $id = $_GET['id'];
        $model = D('PosterMessage');
        if (false !== $model->delete($id)) {
            $this->success(L('delete_ok'));
        } else {
            $this->error(L('delete_error').$model->getDbError());
        }
    }

    function action_screen()
    {
        $this->assign('page', $_GET['page']);
        $this->display();
    }

    public function swfupload()
    {
        $sessid = time();
        $yzh_auth = $_GET['auth'];
        $yzh_auth_key = sysmd5(C('ADMIN_ACCESS').$_SERVER['HTTP_USER_AGENT']);
        $temp_str = authcode($yzh_auth, 'DECODE', $yzh_auth_key);

        $attach = json_decode($temp_str, true);

        $attach['file_types'] = '*.'.str_replace(",",";*.",$attach['file_types']);

        $this->assign('small_upfile_limit', $attach['file_limit']);

        $this->assign('attach', $attach);


        $watermark_enable = $this->SysConfig['watermark_enable'] ? 1 : 0;

        $this->assign('sessid', $sessid);
        $this->assign('watermark_enable', $watermark_enable);
        $this->assign('userid', $this->userid);

        $swf_auth_key = sysmd5($sessid.$this->userid);
        $this->assign('swf_auth_key',$swf_auth_key);

        $this->assign('more',$_GET['more']);

        $this->display();
    }

    public function upload()
    {
        $modelid = I('modelid', 0);
        import("@.ORG.UploadFile");
        $upload = new UploadFile();
        //$upload->supportMulti = false;
        //设置上传文件大小
        $upload->maxSize = $this->SysConfig['attach_maxsize'];

        if (!is_dir(UPLOAD_PATH . 'poster')) {
            dir_create(UPLOAD_PATH . 'poster');
        }
        //设置上传文件类型
        $upload->allowExts = explode(',', $this->SysConfig['attach_allowext']);
        //设置附件上传目录
        $upload->savePath = UPLOAD_PATH . 'poster/' . $modelid . '/';
        //设置上传文件规则
        $upload->saveRule = uniqid;

        //删除原图
        $upload->thumbRemoveOrigin = true;

        if (!$upload->upload()) {
            $this->ajaxReturn(0,$upload->getErrorMsg(),0);
        } else {
            //取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();

            if(I('addwater')){
                import("ORG.Util.Image");
                Image::watermark($uploadList[0]['savepath'].$uploadList[0]['savename'],'',$this->SysConfig);
            }

            $imagearr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');
            $data = array();

            //保存当前数据对象
            $data['filename'] = $uploadList[0]['name'];
            $data['filepath'] = __ROOT__.substr($uploadList[0]['savepath'].strtolower($uploadList[0]['savename']),1);
            $data['filesize'] = $uploadList[0]['size'];
            $data['fileext'] = strtolower($uploadList[0]['extension']);
            $data['isimage'] = in_array($data['fileext'],$imagearr) ? 1 : 0;
            $data['isthumb'] = intval($_REQUEST['isthumb']);

            $this->ajaxReturn($data,L('upload_ok'), '1');
        }
    }

    function preview(){
        $id = I('id');
        $poster = M('Poster')->find($id);

        $this->assign('poster', $poster);
        $this->assign($this->SysConfig);
        $this->display();
    }

    function html()
    {
        $id = I('id');
        $poster = M('Poster')->find($id);

        $this->assign('poster', $poster);

        $this->buildHtml($id,'./poster/','./poster/tpl.php');
    }
}