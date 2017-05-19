<?php

class UrlruleAction extends PublicAction
{

    protected $db;

    function _initialize()
    {
        parent::_initialize();
        $this->db = D('admin/urlrule');
    }

    public function index()
    {
        $model = M('Urlrule');
        if(empty($_REQUEST['where'])){
            $list = $model->select();
        }else{
            $list = $model->where($_REQUEST['where'])->select();
        }

        $this->assign('list', $list);
        $this->display();
    }

    function update()
    {
        if($_POST['setup'])
            $_POST['setup'] = array2string($_POST['setup']);

        $model = D('Urlrule');

        if (false === $model->create()) {
          $this->error($model->getError ());
        }

        if (false !== $model->save ()) {

            savecache('Urlrule');

            $jumpUrl = $_POST['forward'] ? $_POST['forward'] : U(MODULE_NAME.'/index');

            $this->assign('jumpUrl', $jumpUrl);
            $this->success(L('edit_ok'));
        } else {
            $this->success (L('edit_error').': '.$model->getDbError());
        }
    }

    function add()
    {
        $this->display('edit');
    }

    function edit()
    {
        $model = M('Urlrule');
        $pk = ucfirst($model->getPk());
        $id = $_REQUEST[$model->getPk()];

        if(empty($id))
            $this->error(L('do_empty'));

        $do = 'getBy'.$pk;
        $vo = $model->$do( $id );

        if($vo['setup'])
            $vo['setup'] = string2array($vo['setup']);

        $this->assign('vo', $vo);
        $this->display();
    }
}