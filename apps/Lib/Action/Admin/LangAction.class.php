<?php

class LangAction extends PublicAction
{

    protected  $langpath,$lang;

    function _initialize()
    {
        parent::_initialize();
        $this->langpath = LANG_PATH.LANG_NAME.'/';
        $this->fieldpath = APP_PATH.'Lib/Field/';
    }

    public function index()
    {
        $model = M('Lang');
        $list = $model->select();
        $this->assign('list', $list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    function edit()
    {
        if (IS_POST) {
            $model = D('Lang');

            if (false === $model->create()) {
                $this->error($model->getError ());
            }

            if (false !== $model->save()) {
                savecache('Lang');
                $this->success(L('edit_ok'));
            } else {
                $this->success (L('edit_error').': '.$model->getDbError());
            }
        } else {
            $model_db = M('Lang');
            $id = I('id','','intval');

            if(empty($id))
                $this->error(L('do_empty'));

            $vo = $model_db->getById( $id );

            $this->assign('vo', $vo);
            $this->display();
        }
    }

    function param()
    {
        $files = glob($this->langpath.'*');
        $lang_files = array();
        foreach($files as $key => $file) {
            //$filename = basename($file);
            $filename = pathinfo($file);
            $lang_files[$key]['filename'] = $filename['filename'];
            $lang_files[$key]['filepath'] = $file;
            $temp = explode('_',$lang_files[$key]['filename']);
            $lang_files[$key]['name'] = count($temp)>1 ? $temp[0].L('LANG_module') : L('LANG_common') ;
        }
        $this->assign('id', $id);
        $this->assign('lang', LANG_NAME);
        $this->assign('files', $lang_files);
        $this->display();
    }

    function editparam()
    {
        $file = $_REQUEST['file'];
        $value = F($file, $value='', $this->langpath);
        $this->assign('id', $id);
        $this->assign('file', $file);
        $this->assign('lang', LANG_NAME);
        $this->assign('list', $value);
        $this->display();
    }

    function updateparam()
    {
        $file = $_REQUEST['file'];
        unset($_POST[C('TOKEN_NAME')]);

        foreach($_POST as $key=>$r){
            if($r)$data[strtoupper($key)]=$r;
        }
        $r = F($file,$data, $this->langpath);
        if($r){
            $this->success(L('do_ok'));
        }else{
            $this->error(L('add_error'));
        }
    }

    //新增语言
    function add()
    {
        if (IS_POST) {
            $lang_path = LANG_PATH.$_POST['mark'].'/';
            $r = dir_copy(LANG_PATH.'cn/',$lang_path);

            $lang_db = D('Lang');

            if (false === $lang_db->create ()) {
                $this->error($lang_db->getError () );
            }

            $id = $lang_db->add();

            if ($id !==false) {
                $base_config = include($this->fieldpath.'config.php');
                foreach ($base_config as $config) {
                    $config['lang'] = $id;
                    M('Config')->add($config);
                }

                savecache('Lang');
                $this->success('新增成功！');
            } else {
                $this->error('新增失败！: '.$model->getDbError());
            }

        } else {
            $this->display();
        }
    }

    /**
     * 删除
     *
     */
    function delete()
    {
        $lang_db = M('Lang');
        $id = I('get.id', 0, 'intval');

        if ($id) {
            if (false !== $lang_db->delete($id)) {
                savecache('Lang');
                //配置信息
                M('Config')->where('lang='.$id)->delete();
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！: '.$lang_db->getDbError());
            }
        } else {
            $this->error(L('do_empty'));
        }
    }

    public function listorder()
    {
        $lang_db = M('Lang');
        $ids = $_POST['listorders'];

        foreach($ids as $key=>$r) {
            $data['listorder'] = $r;
            $lang_db->where('id='.$key)->save($data);
        }

        $this->success('提交成功!');
    }
}