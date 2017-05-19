<?php

class FormFieldAction extends PublicAction
{

    protected $db;
    protected $Form;

    //初始化
    function _initialize()
    {
        parent::_initialize();
        $this->Form = getCache('Form');
        $this->db = D('FormField');
        $field_pattern = array(
            '0'=> '请选择',
            'email' => '电子邮件地址',
            'url' => '网址',
            'date' => '日期',
            'number'=> '有效的数值',
            'digits'=>  '数字',
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
        $formid = I('request.formid', 0 ,'intval');
        $this->assign('formid', $formid);
        $this->assign('sysfield',array('id,status'));
        $this->assign('nodostatus',array('catid','title','status','createtime'));
        $list = M('FormField')->where("formid=".$formid)->order('listorder ASC')->select();
        $this->assign('list', $list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }


    public function add()
    {
        $formid = I('request.formid', 0 ,'intval');
        $this->assign('formid', $formid);
        if(empty($formid))
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
        $formid = I('post.formid', 0, 'intval');
        if (!$formid) {
            $this->error('模型不能为空！');
        }

        $Form = M('Form')->find($formid);
        $tablename = C('DB_PREFIX').strtolower($Form['tablename']);
        D('');
        $db = DB::getInstance();
        $Fields = $db->getFields($tablename);
        foreach ($Fields as $key =>$r){
            if($key==$name)
                $ishave=1;
        }
        if($ishave) {
            $this->error('当前字段已存在');
        }

        $addfieldsql = $this->get_tablesql($_POST,'add');
        if (!empty($_POST['setup'])) {
            $_POST['setup'] = json_encode($_POST['setup']);
        }
        $_POST['unpostgroup'] = $_POST['unpostgroup'] ?  implode(',',$_POST['unpostgroup']) : '';

        $_POST['status'] = 1;

        $model = D('FormField');

        if (false === $model->create ()) {
            $this->error ( $model->getError () );
        }

        if ($model->add() !==false) {
            savecache('Formfield',$formid);

            if(is_array($addfieldsql)){
                foreach($addfieldsql as $sql){
                    $model->execute($sql);
                }
            }else{
                if($addfieldsql)
                    $model->execute($addfieldsql);
            }
            $this->assign('jumpUrl', U('FormField/index',array('formid' => $formid))) ;
            $this->success (L('add_ok'));
        } else {
            $this->error (L('add_error').': '.$model->getDbError());
        }
    }


    public function edit()
    {
        $feild_db = M('FormField');
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

        $feild_db = D('FormField');
        if (false === $feild_db->create()) {
            $this->error($feild_db->getError());
        }
        if (false !== $feild_db->save()) {
            savecache('FormField',$_POST['formid']);
            if(is_array($editfieldsql)){
                foreach($editfieldsql as $sql){
                    $feild_db->execute($sql);
                }
            }else{
                $r = $feild_db->execute($editfieldsql);
            }

            $this->assign('jumpUrl', U("FormField/index"));
            $this->success(L('edit_ok'));
        } else {
            $this->success(L('edit_error').': '.$model->getDbError());
        }
    }

    function delete()
    {
        $feild_db = M('FormField');
        $id = I('get.id', 0 ,'intval');
        $r = $feild_db->find($id);
        if(empty($r))
            $this->error(L('do_empty'));

        $formid = $r['formid'];
        $field = $r['field'];
        $tablename = C('DB_PREFIX').strtolower($this->Form[$formid]['tablename']);

        $feild_db->execute("ALTER TABLE `$tablename` DROP `$field`");
        $feild_db->delete($id);
        savecache('FormField', $formid);
        $this->success ('删除成功！');
    }

    public function get_tablesql($info,$do)
    {
        $fieldtype = $info['type'];
        if (empty($fieldtype)) {
            $this->error('字段类型不能为空！');
        }
        if ($info['setup']['fieldtype']) {
            $fieldtype = $info['setup']['fieldtype'];
        }
        $formid = $info['formid'];
        $default = $info['setup']['default'];
        $field = $info['field'];
        $Form = M('Form')->find($formid);
        $tablename = C('DB_PREFIX').strtolower($Form['tablename']);
        $maxlength = intval($info['maxlength']);
        $numbertype = $info['setup']['numbertype'];
        $oldfield = $info['oldfield'];

        if ($do=='add') {
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

            case 'typeid':
                $sql = "ALTER TABLE `$tablename` $do `$field` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'datetime':
                $sql = "ALTER TABLE `$tablename` $do `$field` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
                break;

            case 'editor':
                $sql = "ALTER TABLE `$tablename` $do `$field` MEDIUMTEXT NOT NULL";
                break;

            case 'image':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;

            case 'file':
                $sql = "ALTER TABLE `$tablename` $do `$field` VARCHAR( 80 ) NOT NULL DEFAULT ''";
                break;
        }
        return $sql;
    }

    //字段排序
    public function listorder()
    {
        $feild_db = M('FormField');
        $ids = $_POST['listorders'];

        foreach ($ids as $key=>$r) {
            $data['listorder']=$r;
            $feild_db->where('id='.$key)->save($data);
        }

        $this->success('提交成功!');
    }
}