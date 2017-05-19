<?php

class FieldAction extends PublicAction
{

    protected $db;

    //初始化
    function _initialize()
    {
        parent::_initialize();
        $this->db = D('Field');
        $field_pattern = array(
            '0'=> '请选择',
            'email' => '电子邮件地址',
            'url' => '网址',
            'date' => '日期',
            'number'=> '有效的数值',
            'digits'=>  '数字',
            'creditcard'=> '信用卡号码',
            'equalTo'=> '再次输入相同的值.',
            'ip4'=>  'IP地址',
            'mobile'=> '手机号码',
            'zipcode'=> '邮编',
            'qq'=> 'QQ号码',
            'idcard'=> '身份证号',
            'chinese'=> '中文字符',
            'cn_username'=> '中文英文和数字和下划线',
            'tel'=> '电话号码',
            'english'=> '英文',
            'en_num'=> '英文和数字和下划线',
        );
        $this->assign('field_pattern', $field_pattern);
        $this->assign('options', array(1=>L('yes'),0=>L('no')));
        $role = getCache('Role');
        foreach((array)$role as $key=>$c){
            $usergroup[$key] = $c['name'];
        }
        $this->assign('usergroup', $usergroup);
    }

    public function index()
    {
        $modelid = I('request.modelid', 0 ,'intval');
        $this->assign('modelid', $modelid);
        $this->assign('sysfield',array('catid','userid','username','title','thumb','keywords','description','posid','url'));
        $this->assign('nodostatus',array('catid','createtime'));
        $list = M('Field')->where("modelid=".$modelid)->order('listorder ASC')->select();
        $this->assign('list', $list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }


    public function add()
    {
        $modelid = I('request.modelid', 0 ,'intval');
        $this->assign('modelid', $modelid);
        if(empty($modelid))
            $this->error(L('do_empty'));

        if($_GET['isajax']){
            $this->assign($_GET);
            $this->assign($_POST);
            $this->display('type');
            exit;
        }
        $this->display();
    }

    function insert()
    {
        $name = $_POST['field'];
        $modelid = I('post.modelid', 0, 'intval');
        if (!$modelid) {
            $this->error('模型不能为空！');
        }
        $tablename = C('DB_PREFIX').strtolower($this->Model[$modelid]['tablename']);
        D('');
        $db =   DB::getInstance();
        $Fields = $db->getFields($tablename);

        $ishave = 0;
        foreach ($Fields as $key =>$r){
            if($key==$name)
                $ishave=1;
        }
        if($ishave) {
            $this->error('当前字段已存在');
        }

        $addfieldsql = $this->get_tablesql($_POST,'add');
        if($_POST['setup']) {
            $_POST['setup'] = json_encode($_POST['setup']);
        }
        $_POST['unpostgroup'] = $_POST['unpostgroup'] ?  implode(',',$_POST['unpostgroup']) : '';
        $_POST['status'] = 1;

        $model = D('Field');

        if (false === $model->create()) {
            $this->error ( $model->getError () );
        }

        if ($model->add() !==false) {
            savecache('Field',$_POST['modelid']);

            if(is_array($addfieldsql)){
                foreach($addfieldsql as $sql){
                    $model->execute($sql);
                }
            }else{
                if($addfieldsql)$model->execute($addfieldsql);
            }
            $this->assign('jumpUrl', U('Field/index',array('modelid' => $modelid))) ;
            $this->success (L('add_ok'));
        } else {
            $this->error (L('add_error').': '.$model->getDbError());
        }
    }


    public function edit()
    {
        $feild_db = M('Field');
        $id = $_GET['id'];

        if(empty($id))
            $this->error(L('do_empty'));

        $vo = $feild_db->getById($id);

        if (!empty($vo['setup']))
            $vo['setup'] = json_decode($vo['setup'],true);

        $this->assign('vo', $vo);
        $this->display();
    }


    function update()
    {
        $editfieldsql = $this->get_tablesql($_POST,'edit');

        if (!empty($_POST['setup'])) {
            $_POST['setup'] = json_encode($_POST['setup']);
        }

        //解决在MAGIC_QUOTES_GPC开启的情况下，'\'消失的问题
        if (MAGIC_QUOTES_GPC) {
            $_POST['setup'] = addslashes($_POST['setup']);
        }

        $feild_db = D('Field');
        if (false === $feild_db->create()) {
            $this->error ( $feild_db->getError () );
        }
        if (false !== $feild_db->save()) {
            savecache('Field',$_POST['modelid']);
            if(is_array($editfieldsql)){
                foreach($editfieldsql as $sql){
                    $feild_db->execute($sql);
                }
            }else{
                $feild_db->execute($editfieldsql);
            }

            $this->assign('jumpUrl', U("Field/index"));
            $this->success(L('edit_ok'));
        } else {
            $this->success(L('edit_error').': '.$feild_db->getDbError());
        }
    }


    function delete()
    {
        $feild_db = M('Field');
        $id = I('get.id', 0 ,'intval');
        $r = $feild_db->find($id);
        if(empty($r))
            $this->error(L('do_empty'));

        $modelid = $r['modelid'];
        $field = $r['field'];
        $tablename = C('DB_PREFIX').strtolower($this->Model[$modelid]['tablename']);
        //echo "ALTER TABLE `$tablename` DROP `$field`";exit;
        $feild_db->execute("ALTER TABLE `$tablename` DROP `$field`");
        $feild_db->delete($id);
        savecache('Field', $modelid);
        $this->success ('删除成功！');
    }

    public function status()
    {
        $id = I('get.id', 0 ,'intval');
        if($this->db->save($_GET)){
            $r = $this->db->find($id);
            savecache('Field',$r['modelid']);
            $this->success(L('do_ok'));
        }else{
            $this->error(L('do_error'));
        }
    }


    public function get_tablesql($info,$do)
    {
        $fieldtype = $info['type'];
        if ($info['setup']['fieldtype']) {
            $fieldtype = $info['setup']['fieldtype'];
        }
        $modelid = $info['modelid'];
        $default = $info['setup']['default'];
        $field = $info['field'];
        $tablename = C('DB_PREFIX').strtolower($this->Model[$modelid]['tablename']);
        $maxlength = intval($info['maxlength']);
        $minlength = intval($info['minlength']);
        $numbertype = $info['setup']['numbertype'];
        $oldfield = $info['oldfield'];

        if ($do == 'add') {
            $do = ' ADD ';
        } else {
            $do = " CHANGE `$oldfield` ";
        }

        switch($fieldtype) {
            case 'varchar':
                if(!$maxlength) $maxlength = 255;
                $maxlength = min($maxlength, 255);
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( $maxlength ) NOT NULL DEFAULT '$default'";
                break;

            case 'title':
                if(!$maxlength) $maxlength = 255;
                $maxlength = min($maxlength, 255);
                $sql[] = "ALTER TABLE `$tablename` $do `title` VARCHAR( $maxlength ) NOT NULL DEFAULT '$default'";
                $sql[] = "ALTER TABLE `$tablename` $do `title_style` VARCHAR( 40 ) NOT NULL DEFAULT ''";
                $sql[] = "ALTER TABLE `$tablename` $do `thumb` VARCHAR( 100 ) NOT NULL DEFAULT ''";
                break;

            case 'catid':
                $sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'number':
                $decimaldigits = $info['setup']['decimaldigits'];
                $default = $decimaldigits == 0 ? intval($default) : floatval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` ".($decimaldigits == 0 ? 'INT' : 'decimal( 10,'.$decimaldigits.' )')." ".($numbertype ==1 ? 'UNSIGNED' : '')."  NOT NULL DEFAULT '$default'";
                break;

            case 'tinyint':
                if(!$maxlength) $maxlength = 3;
                $maxlength = min($maxlength,3);
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` TINYINT( $maxlength ) ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'smallint':
                $default = intval($default);
                if(!$maxlength) $maxlength = 8;
                $maxlength = min($maxlength,8);
                $sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT( $maxlength ) ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'int':
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` INT ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'mediumint':
                $default = intval($default);
                $sql = "ALTER TABLE `$tablename` $do `$field` INT ".($numbertype ==1 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$default'";
                break;

            case 'mediumtext':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;

            case 'text':
                $sql = "ALTER TABLE `$tablename` $do `$field` TEXT NOT NULL";
                break;

            case 'posid':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR(50) NOT NULL DEFAULT '0'";
                break;

            case 'typeid':
                $sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'datetime':
                $sql = "ALTER TABLE `$tablename` $do `$field` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'editor':
                $sql = "ALTER TABLE `$tablename` $do `$field` TEXT NOT NULL";
                break;

            case 'image':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;

            case 'images':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;

            case 'file':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;

            case 'files':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;
        }
        return $sql;
    }

    //字段排序
    public function listorder()
    {
        $feild_db = M('Field');
        $ids = $_POST['listorders'];

        foreach ($ids as $key=>$r) {
            $data['listorder']=$r;
            $feild_db->where('id='.$key)->save($data);
        }

        $this->success('提交成功!');
    }
}