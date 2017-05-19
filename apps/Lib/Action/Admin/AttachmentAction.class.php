<?php

class AttachmentAction extends  PublicAction
{

    protected $db;
    protected $userid;

    function _initialize()
    {
        parent::_initialize();
        $this->db = M('Attachment');
        $this->userid = $_SESSION['admin']['id'];
    }

    public function index()
    {
        import('@.ORG.Page');
        $attachment_db = M('Attachment');

        if(empty($_REQUEST['start_time'])){
            $start_time = 0;
        } else {
            $start_time = strtotime($_REQUEST['start_time']);
        }
        if(empty($_REQUEST['end_time'])){
            $end_time = time();
        } else {
            $end_time = strtotime($_REQUEST['end_time']);
        }

        $map['createtime'] = array(array('gt',$start_time),array('lt',$end_time));


        $count = $attachment_db->where($map)->count();
        $page = new Page($count,30);
        $imagearr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');

        $show = $page->show();
        $this->assign("page",$show);
        $list = $this->db->where($map)->order('aid desc')->limit($page->firstRow.','.$page->listRows)->select();
        foreach((array)$list as $key=>$r){
            $list[$key]['thumb'] = in_array($r['fileext'],$imagearr) ? $r['filepath'] : __ROOT__.'/public/images/ext/'.$r['fileext'].'.png';
            $list[$key]['filesize'] = byte_format($list[$key]['filesize']);
        }

        $this->assign('list',$list);
        $this->assign($_REQUEST);


        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    public function edit()
    {
        $attachment_db = M('Attachment');
        if (IS_POST) {
            $_POST['updatetime'] = time();
            if (false === $attachment_db->create()) {
                $this->error($attachment_db->getError());
            }

            if (false !== $attachment_db->save()) {
                $this->success('修改成功！');
            }
        } else {
            $aid = $_REQUEST['aid'];

            $vo = $attachment_db->find($aid);

            $form = new Form($vo);

            $this->assign($_REQUEST);
            $this->assign('vo', $vo);
            $this->assign('form', $form);

            $watermark_enable = $this->SysConfig['watermark_enable'] ? 1 : 0;
            $this->assign('watermark_enable', $watermark_enable);

            $this->display();
        }
    }

    public function swfupload()
    {
        $sessid = time();
        $yzh_auth = $_GET['auth'];
        $yzh_auth_key = sysmd5(C('ADMIN_ACCESS').$_SERVER['HTTP_USER_AGENT']);
        $temp_str = authcode($yzh_auth, 'DECODE', $yzh_auth_key);

        $attach = json_decode($temp_str, true);

        $attach['file_types'] = '*.'.str_replace(",",";*.",$attach['file_types']);

        $count = $this->db->where('status=0 and userid ='.$this->userid)->count();
        $this->assign('no_use_files',$count);

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
        import("@.ORG.UploadFile");
        $upload = new UploadFile();
        //$upload->supportMulti = false;
        //设置上传文件大小
        $upload->maxSize = $this->SysConfig['attach_maxsize'];
        $upload->autoSub = true;

        $upload->subType = 'date';
        $upload->dateFormat = 'Ym';
        //设置上传文件类型
        $upload->allowExts = explode(',', $this->SysConfig['attach_allowext']);
        //设置附件上传目录
        $upload->savePath = UPLOAD_PATH;
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
                //$this->Config['watermark_enable']  $_REQUEST['addwater']
                import("ORG.Util.Image");
                Image::watermark($uploadList[0]['savepath'].$uploadList[0]['savename'],'',$this->SysConfig);
            }

            $imagearr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');
            $data = array();

            $attachment_db = M('Attachment');
            $modelid = I('modelid', -1);
            $catid = I('catid', 0);
            //保存当前数据对象
            $data['modelid'] = $modelid;
            $data['catid'] = $catid;
            $data['userid'] = $_SESSION['admin']['id'];
            $data['filename'] = $uploadList[0]['name'];
            $data['filepath'] = __ROOT__.substr($uploadList[0]['savepath'].strtolower($uploadList[0]['savename']),1);
            $data['filesize'] = $uploadList[0]['size'];
            $data['fileext'] = strtolower($uploadList[0]['extension']);
            $data['isimage'] = in_array($data['fileext'],$imagearr) ? 1 : 0;
            $data['isthumb'] = intval($_REQUEST['isthumb']);
            $data['createtime'] = time();
            $data['uploadip'] = get_client_ip();
            $aid = $attachment_db->add($data);
            $returndata['aid']    = $aid;
            $returndata['filepath'] = $data['filepath'];
            $returndata['fileext']  = $data['fileext'];
            $returndata['isimage']  = $data['isimage'];
            $returndata['filename'] = $data['filename'];
            $returndata['filesize'] = $data['filesize'];

            $this->ajaxReturn($returndata,L('upload_ok'), '1');
        }
    }

    public function filelist()
    {
        import('@.ORG.Page' );
        $attachment_db = M('Attachment');
        if(empty($_REQUEST['start_time'])){
            $start_time = '';
        } else {
            $start_time = strtotime($_REQUEST['start_time']);
        }
        if(empty($_REQUEST['end_time'])){
            $end_time = time();
        } else {
            $end_time = strtotime($_REQUEST['end_time']);
        }

        $map['createtime'] = array(array('gt',$start_time),array('lt',$end_time));

        $count = $attachment_db->where($map)->count();

        $Page = new Page($count,10);
        $imagearr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');

        $Page->urlrule = 'javascript:ajaxload('.$_REQUEST['typeid'].',{$page},\''.$_REQUEST['inputid'].'\',\''.$_REQUEST['start_time'] .'\',\''.$_REQUEST['end_time'] .'\');';
        $show = $Page->show();
        $this->assign("page",$show);

        $list = $attachment_db->where($map)->order('aid desc')
            ->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach((array)$list as $key=>$r){
            $list[$key]['thumb']=in_array($r['fileext'],$imagearr) ? $r['filepath'] : __ROOT__.'/public/images/ext/'.$r['fileext'].'.png';
        }
        $this->assign('list',$list);
        $this->assign($_REQUEST);

        $this->display();
    }

    public function addwater()
    {
        $filepath = I('get.filepath');
        import("ORG.Util.Image");
        $r = Image::watermark('.'.$filepath,'',$this->SysConfig);
        if ($r) {
            $this->success('添加水印成功');
        } else {
            $this->error('添加水印失败');
        }
    }


    //设置swfupload上传的json格式cookie
    public function swfupload_json()
    {
        $arr = array();
        $arr['aid'] = I('get.aid', 0, 'intval');
        $arr['src'] = I('get.src', '', 'trim');
        $arr['filename'] = I('get.filename');
        return $this->upload_json($arr['aid'], $arr['src'], $arr['filename']);
    }

    /**
     * 设置upload上传的json格式cookie
     * @param type $aid 附件ID
     * @param type $src 附件地址
     * @param type $filename 附件名称
     * @return boolean 返回布尔值
     */
    public function upload_json($aid, $src, $filename)
    {
        $arr['aid'] = $aid;
        $arr['src'] = trim($src);
        $arr['filename'] = $filename;
        $json_str = json_encode($arr);
        $att_arr_exist = cookie('att_json');
        $att_arr_exist_tmp = explode('||', $att_arr_exist);
        if (is_array($att_arr_exist_tmp) && in_array($json_str, $att_arr_exist_tmp)) {
            return true;
        } else {
            $json_str = $att_arr_exist ? $att_arr_exist . '||' . $json_str : $json_str;
            cookie('att_json', $json_str);
            return true;
        }
    }


    //删除swfupload上传的json格式cookie
    public function swfupload_json_del()
    {
        $arr['aid'] = I('get.aid', 0, 'intval');
        $arr['src'] = I('get.src', '', '');
        $arr['filename'] = urlencode(I('get.filename', '', ''));
        $json_str = json_encode($arr);
        $att_arr_exist = cookie('att_json');
        cookie('att_json', NULL);
        $att_arr_exist = str_replace(array($json_str, '||||'), array('', '||'), $att_arr_exist);
        $att_arr_exist = preg_replace('/^\|\|||\|\|$/i', '', $att_arr_exist);
        cookie('att_json', $att_arr_exist);
    }
}