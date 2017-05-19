<?php

class ModelAction extends PublicAction
{

    protected $db;
    protected $path;

    function _initialize()
    {
        parent::_initialize();
        $this->path = APP_PATH.'Lib/Field/';
        $this->db = D('Model');
        $this->Role = getCache('Role');
        $this->assign('Role',$this->Role);
    }

    function index()
    {
        if($_REQUEST ['type']){
            $_REQUEST['where'] = 'type='.intval($_REQUEST ['type']);
        }else{
            $_REQUEST['where'] = 'type=1';
        }

        $model_db = M('Model');
        if(empty($_REQUEST['where'])){
            $list = $model_db->select();
        }else{
            $list = $model_db->where($_REQUEST['where'])->select();
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
        $model_db = M('Model');
        $id = I('get.id', 0, 'intval');

        if(empty($id))
            $this->error(L('do_empty'));

        $vo = $model_db->getById( $id );

        $this->assign('vo', $vo);
        $this->display();
    }

    function update()
    {
        $model_db = D('Model');

        if (false === $model_db->create()) {
            $this->error($model_db->getError ());
        }

        if (false !== $model_db->save()) {

            savecache('Model');

            $this->success(L('edit_ok'));
        } else {
            $this->error(L('edit_error').': '.$model_db->getDbError());
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
            $this->error('此模型已存在!');
        }

        $_POST['tablename'] = ucfirst($_POST['tablename']);
        $model_db = $this->db;
        if (false === $model_db->create()) {
            $this->error($model_db->getError());
        }
        $modelid = $model_db->add();
        if(empty($modelid))
            $this->error(L('add_error').': '.$model_db->getDbError());

        $field_db = D('Field');
        if(empty($_POST['emptytable'])){
            $db->execute("CREATE TABLE `".$tablename."` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
                `userid` int(8) unsigned NOT NULL DEFAULT '0',
                `title` varchar(120) NOT NULL DEFAULT '',
                `title_style` varchar(40) NOT NULL DEFAULT '',
                `thumb` varchar(100) NOT NULL DEFAULT '',
                `keywords` varchar(120) NOT NULL DEFAULT '',
                `description` text NOT NULL,
                `content` mediumtext NOT NULL,
                `url` varchar(60) NOT NULL DEFAULT '',
                `template` varchar(40) NOT NULL DEFAULT '',
                `posid` varchar(50) NOT NULL DEFAULT '',
                `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `readgroup` varchar(100) NOT NULL DEFAULT '',
                `listorder` int(10) unsigned NOT NULL DEFAULT '0',
                `hits` int(11) unsigned NOT NULL DEFAULT '0',
                `createtime` int(11) unsigned NOT NULL DEFAULT '0',
                `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
                `lang` tinyint(1) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `status` (`id`,`status`,`listorder`),
                KEY `catid` (`id`,`catid`,`status`),
                KEY `listorder` (`id`,`catid`,`status`,`listorder`)
              ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");

            $model_filed = include($this->path.'catid.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'title.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'keywords.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'description.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'createtime.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'content.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'hits.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'readgroup.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'posid.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'recommend.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'template.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'status.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
        }else{
            $db->execute("CREATE TABLE `".$tablename."` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `userid` int(8) unsigned NOT NULL DEFAULT '0',
                `url` varchar(60) NOT NULL DEFAULT '',
                `listorder` int(10) unsigned NOT NULL DEFAULT '0',
                `createtime` int(11) unsigned NOT NULL DEFAULT '0',
                `updatetime` int(11) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
            $model_filed = include($this->path.'createtime.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
            $model_filed = include($this->path.'status.php');
            $model_filed['modelid'] = $modelid;
            $field_db->add($model_filed);
        }

        if ($modelid  !==false) {

            //新增相应菜单
            $menu_db = M('Menu');
            $data = array();
            $data['parentid'] = 185;
            $data['model'] = $_POST['tablename'];
            $data['group'] = 'Admin';
            $data['action'] = 'index';
            $data['status'] = 1;
            $data['name'] =  $_POST['name'];
            $data['icon']= 'fa fa-th-large';
            $mene_id = $menu_db->data($data)->add();
            $data['parentid'] = $mene_id;
            $data['action'] = 'add';
            $data['status'] = 1;
            $data['name'] = '新增';
            $menu_db->data($data)->add();
            $data['parentid'] = $mene_id;
            $data['action'] = 'edit';
            $data['status'] = 1;
            $data['name'] = '编辑';
            $menu_db->data($data)->add();

            //新增相应节点
            $node_db = M('Node');
            $data['groupid'] = 184;
            $data['status'] = 1;
            $data['level'] = 2;
            $data['pid'] = 1;
            $data['title'] = $_POST['name'];
            $data['name'] = $_POST['tablename'];
            $node_pid = $node_db->data($data)->add();
            $data['pid'] = $node_pid;
            $data['level'] = 3;
            $data['name'] ='index';
            $data['title'] = '查看';
            $node_db->data($data)->add();
            $data['name'] ='add';
            $data['title'] = '新增';
            $node_db->data($data)->add();
            $data['name'] ='edit';
            $data['title'] = '编辑';
            $node_db->data($data)->add();
            $data['name'] ='delete';
            $data['title'] = '删除';
            $node_db->data($data)->add();
            $data['name'] ='push';
            $data['title'] = '推送';
            $node_db->data($data)->add();
            $data['name'] ='remove';
            $data['title'] = '移动';
            $node_db->data($data)->add();
            $data['name'] ='listorder';
            $data['title'] = '排序';
            $node_db->data($data)->add();
            savecache('Model');
            savecache('Menu');
            savecache('Node');
            savecache('Field',$modelid);
            $this->assign('jumpUrl', U('Model/index') );
            $this->success (L('add_ok'));
        } else {
            $this->error (L('add_error').': '.$model_db->getDbError());
        }
    }

    function delete()
    {
        $model_db = M('Model');
        $id = I('get.id', 0, 'intval');
        $r = $model_db->find($id);
        if(empty($r))
            $this->error(L('do_empty'));

        $tablename = C('DB_PREFIX').strtolower($this->Model[$id]['tablename']);
        $m = $model_db->delete($id);
        if($m){
            $model_db->execute("DROP TABLE IF EXISTS `".$tablename."`");
            $modelname = ucfirst($this->Model[$id]['tablename']);
            $menu_db = M('Menu');
            $menu_db->where("model='".$modelname."'")->delete();
            $field_db = M('Field');
            $field_db->where('modelid='.$id)->delete();
            $node_db = M('Node');
            $node = $node_db->where("groupid=3 and name='".$modelname."'")->find();
            $strChildId = $this->getStrChildId($node['id']);
            $node_db->delete($strChildId);

            savecache('Model');
            savecache('Menu');
            savecache('Node');
            @unlink(RUNTIME_PATH.'Data/Field_'.$id.'.php');
            $this->success(L('do_ok'));
        }
    }

    /*状态*/
    public function status()
    {
        $model_db = D('Model');
        if($model_db->save($_GET)){
            savecache('Model');
            $this->success('提交成功!');
        }else{
            $this->error('提交失败！');
        }
    }

    //获取当前菜单id和子栏目id
    function getStrChildId($id)
    {
        $node_db = M('Node');
        $strChildId = $id;
        $list = $node_db->where('pid='.$id)->select();
        if (!empty($list)) {
            foreach ($list as $val) {
                $strChildId = $strChildId.','.$this->getStrChildId($val['id']);
            }
        }

        return $strChildId;
    }
}