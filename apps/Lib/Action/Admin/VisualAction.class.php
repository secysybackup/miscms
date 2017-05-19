<?php

class VisualAction extends PublicAction
{

    protected $db;

    function _initialize()
    {
        parent::_initialize();
        $this->db = M('Block');
    }

    function index()
    {
        $data = M('WapConfig')->select();

        $config = array();
        foreach ($data as $val) {
            $config[$val['varname']] = $val['value'];
        }

        $sysconfig = getCache("Sysconfig");
        $this->assign('sysconfig', $sysconfig);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $client = I('get.client','pc');
        if ($client == 'wap') {
            $client_url = 'http://'.$sysconfig['SITE_WAP_DOMAIN'];

            $block_list = M('Block')->where('`groupid`=2 and `lang`='.LANG_ID)->select();
            $this->assign('block_list', $block_list);
        } else {
            $client_url = 'http://'.$sysconfig['SITE_DOMAIN'];
        }

        $this->assign('client_url',$client_url);

        if ($client == 'wap') {
            $this->display('wap');
        } else {
            $this->display('pc');
        }
    }

    function edit()
    {
        $editableType = I('editableType');
        $editableId = I('editableId');

        switch ($editableType) {
            case 'block':
                $idArr = explode(',', $editableId);

                $blockInfo = array();
                foreach ($idArr as $id) {
                    $blockInfo[] = M('Block')->where('id='.$id)->find();
                }

                $this->assign('blockInfo', $blockInfo);
                $this->display('block');
                break;
            case 'slide':
                $editable = M('SlideData')->find($editableId);

                $this->assign('editable', $editable);
                $this->display('slide');
                break;
            case 'config':
                $editable = M('config')->find($editableId);

                $this->assign('editable', $editable);
                $this->display('config');
                break;
            default:
                break;
        }

    }

    function update()
    {
        $editableType = I('editableType');

        switch ($editableType) {
            case 'block':
                $block_id_list = $_POST['block_id_list'];
                $block_value_list = $_POST['block_value_list'];

                foreach ($block_value_list as $key => $value) {
                    if (!empty($value)) {
                        $blcok['content'] = stripslashes($value);
                        M('Block')->where('id=' . $block_id_list[$key])->save($blcok);
                    }
                }
                if($_POST['aid']) {
                    $Attachment = M('attachment');
                    $aids =  implode(',',$_POST['aid']);
                    $data['model'] = -1;
                    $data['status'] = 1;
                    $Attachment->where("aid in (".$aids.")")->save($data);
                }

                $this->success('提交成功');
                break;
            case 'slide':
                $model = D('slide_data');
                if (false === $model->create()) {
                    $this->error($model->getError());
                }

                if (false !== $model->save()) {

                    if($_POST['aid']){
                        $Attachment = M('attachment');
                        $aids = implode(',',$_POST['aid']);
                        $data['id']= $_POST['id'];
                        $data['catid']= $_POST['fid'];
                        $data['status']= '1';
                        $Attachment->where("aid in (".$aids.")")->save($data);
                    }
                    $this->success(L('edit_ok'));
                } else {
                    $this->success(L('edit_error').': '.$model->getDbError());
                }
                break;
            case 'config':
                $model = M('Config');
                $where ="";

                $where.= " AND lang=".LANG_ID;

                $sta = false;
                foreach($_POST as $key=>$value){
                    $data['value'] = $value;
                    $f = $model->where("varname='".$key."'".$where)->save($data);
                    if ($f) {
                        $sta = true;
                    }
                }

                if($_POST['aid']) {
                    $Attachment = M('attachment');
                    $aids =  implode(',',$_POST['aid']);
                    $data['model'] = -1;
                    $data['status'] = 1;
                    $Attachment->where("aid in (".$aids.")")->save($data);
                }
                savecache('Config');

                if($sta){
                    $this->success('保存成功!');
                }else{
                    $this->error('没有发生更改!');
                }
                break;
            default:
                break;

        }
    }

    function datacall()
    {
        $config = M('Config')->select();
        foreach($config as $key=>$r) {

            //公司信息
            if($r['group']==2 && $r['lang']==LANG_ID){
                $config_list[$r['varname']] = $r;
            }
        }
        $this->assign('config_list',$config_list);

        $this->display();
    }
}