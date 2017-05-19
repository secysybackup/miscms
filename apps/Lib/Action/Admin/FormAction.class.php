<?php

class FormAction extends PublicAction
{

    protected $db;
    protected $Form;

    function _initialize()
    {
        parent::_initialize();
        $this->Form = getCache('Form');
        $this->db = D('Form');
    }

    function index()
    {
        $form_db = M('Form');
        if(empty($_REQUEST['where'])){
            $list = $form_db->select();
        }else{
            $list = $form_db->where($_REQUEST['where'])->select();
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('list', $list);
        $this->display();
    }

    function add()
    {
        if(empty($_GET['type']))
            $_GET['type']=1;
        $this->display();
    }

    function edit()
    {
        $form_db = M('Form');
        $id = I('get.id', 0, 'intval');

        if(empty($id))
            $this->error(L('do_empty'));

        $vo = $form_db->getById( $id );

        $this->assign('vo', $vo);
        $this->display();
    }

    function update()
    {

        $form_db = D('Form');

        if (false === $form_db->create()) {
            $this->error($form_db->getError ());
        }

        if (false !== $form_db->save()) {

            savecache('Form');

            $this->success(L('edit_ok'));
        } else {
            $this->error(L('edit_error').': '.$form_db->getDbError());
        }
    }

    function insert()
    {
        $tablename = I('post.tablename');
        $tablename = C('DB_PREFIX').strtolower($tablename);

        D('');
        $db =   DB::getInstance();
        $tables = $db->getTables();
        if(in_array($tablename,$tables)){
            $this->error('此表单已存在!');
        }

        $_POST['tablename'] = ucfirst($_POST['tablename']);
        $form_db = $this->db;
        if (false === $form_db->create()) {
            $this->error($form_db->getError());
        }
        $formid = $form_db->add();
        if(empty($formid))
            $this->error (L('add_error').': '.$form_db->getDbError());

        $db->execute("CREATE TABLE `".$tablename."` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
            `userid` int(8) unsigned NOT NULL DEFAULT '0',
            `createtime` int(11) unsigned NOT NULL DEFAULT '0',
            `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

        if ($formid  !==false) {
            savecache('Form');
            $this->assign('jumpUrl', U('Form/index') );
            $this->success (L('add_ok'));
        } else {
            $this->error (L('add_error').': '.$form_db->getDbError());
        }
    }

    function delete()
    {
        $id = I('get.id', 0, 'intval');
        $r = $this->db->find($id);
        if(empty($r)) $this->error(L('do_empty'));

        $tablename = C('DB_PREFIX').strtolower($this->Form[$id]['tablename']);
        $m = $this->db->delete($id);
        if($m){
            $this->db->execute("DROP TABLE IF EXISTS `".$tablename."`");

            $field_db = M('FormField');
            $field_db->where('formid='.$id)->delete();

            savecache('Form');
            @unlink(RUNTIME_PATH.'Data/FormField_'.$id.'.php');
            $this->success(L('do_ok'));
        }
    }

    /*状态*/
    public function status()
    {
        $form_db = D('Form');
        if($form_db->save($_GET)){
            savecache('Model');
            $this->success('提交成功!');
        }else{
            $this->error('提交失败！');
        }
    }

    function content()
    {
        $formid = I('get.formid', 0 ,'intval');

        if (!$formid) {
            $this->error('缺少必要的参数！');
        }

        $tablename = $this->Form[$formid]['tablename'];
        $listfields = explode(',',$this->Form[$formid]['listfields']);

        $fields = getCache('FormField_'.$formid);
        $this->assign('fields', $fields);
        $this->assign('formid', $formid);
        $this->assign('listfields', $listfields);

        $form_db = M($tablename);

        $map = array();
        $this->assign($_REQUEST);

        //取得满足条件的记录总数
        $count = $form_db->where($map)->count('id');

        if ($count > 0) {
            import("@.ORG.Page");

            $p = new Page($count, 15);

            //分页查询数据
            $voList = $form_db->where($map)->limit($p->firstRow . ',' . $p->listRows)->order('id desc')->select ( );

            //分页跳转的时候保证查询条件
            foreach ( $map as $key => $val ) {
                if (! is_array ( $val )) {
                    $p->parameter .= "$key=" . urlencode ( $val ) . "&";
                }
            }
            $url_param = $_GET;
            $url_param[C('VAR_PAGE')]='{$page}';
            $p->urlrule = U('Form/content', $url_param);
            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $voList );
            $this->assign('page', $page );
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    function contentedit()
    {
        $formid = I('get.formid', 0 ,'intval');

        if (!$formid) {
            $this->error('缺少必要的参数！');
        }

        $tablename = $this->Form[$formid]['tablename'];

        $form_db = M($tablename);

        $fields = getCache('FormField_'.$formid);

        foreach($fields as $key => $res){
            $res['setup'] = json_decode($res['setup'],true);
            $fields[$key] = $res;
        }

        $this->assign('fields',$fields);

        if (IS_POST) {

            if (false === $form_db->create()) {
                $this->error($form_db->getError());
            }

            // 更新数据
            $list = $form_db->save();

            if (false !== $list) {

                $this->success(L('edit_ok'));
            } else {
                //错误提示
                $this->error(L('edit_error').': '.$model->getDbError());
            }
        } else {
            $id = I('get.id', 0 ,'intval');

            if (!$id) {
                $this->error('缺少必要的参数！');
            }

            $vo = $form_db->getById($id);

            $form = new Form($vo);

            $this->assign($_REQUEST);
            $this->assign('vo', $vo);
            $this->assign('form', $form);

            $this->display();
        }
    }

    function contentdelete()
    {
        $formid = I('get.formid', 0 ,'intval');
        $id = I('get.id', 0 ,'intval');

        if (!$formid || !$id) {
            $this->error('缺少必要的参数！');
        }

        $tablename = $this->Form[$formid]['tablename'];

        $form_db = M($tablename);

        if(false!==$form_db->delete($id)){
            $this->success(L('delete_ok'));
        }else{
            $this->error(L('delete_error').': '.$form_db->getDbError());
        }

    }
}