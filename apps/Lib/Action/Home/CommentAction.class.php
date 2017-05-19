<?php

class CommentAction extends PublicAction
{
    protected $db,$fields;

    public function _initialize() {

        parent::_initialize();

        $this->modelid = $_REQUEST['modelid'];


        if($this->moduleid){
                $this->assign ('modelid',$this->modelid);
                $module =$this->Model[$this->modelid]['name'];
                $this->db = D($module);
                $fields = F($this->moduleid.'_Field');
                foreach ($fields as $key => $res) {
                        if ($res['unpostgroup']) {
                                $res['unpostgroup'] = explode(',',$res['unpostgroup']);
                        }
                        if (!in_array($this->_groupid,$res['unpostgroup'])  && $res['status']) {
                                $res['setup']=string2array($res['setup']);
                                $this->fields[$key]=$res;
                        }
                }
                unset($fields);
                unset($res);
                $this->assign ('fields',$this->fields);
        }
    }

    public function index($catid='') {
        if(empty($catid))
            $catid = intval($_REQUEST['id']);

        if($catid){
            $cat = $this->Categorys[$catid];
            $bcid = explode(",",$cat['arrparentid']);
            $bcid = $bcid[1];
            if($bcid == '') $bcid=intval($catid);
            if(empty($module))$module=$cat['module'];
            $this->assign('module_name',$module);
            unset($cat['id']);
            $this->assign($cat);
            $this->assign('catid',$catid);
        }

        $this->display();
    }

    /**
     * 录入
     *
     */
    public function insert($userid=0,$username='',$groupid=0) {

        if($_POST['moduleid']==8||$_POST['moduleid']==6){

            $feedback = M($this->module[$this->moduleid]['name']);
            $count = $feedback->where(' ip=\''.$_POST['ip'].'\' AND createtime > UNIX_TIMESTAMP()-86400')->count();
            if($count>5){
                $this->error('对不起，一天一人只能5条！请明天发送或直接给我们联系！');
                exit;
            }
        }

        $_POST['ip'] = get_client_ip();
        $userid = 0;
        $username = '';
        $groupid = 0;
        $model = M($this->module[$this->moduleid]['name']);

        $fields = $this->fields;

        if((md5($_POST['verifyCode']) != $_SESSION['verify'])){
            $this->assign('script','javascript:history.go(-1);');
            $this->error(L('ERROR_VERIFY'));
        }

        $_POST = checkfield($fields,$_POST);

        if(empty($_POST)){
            $this->error(L('do_empty'),$url);
        }

        $_POST['createtime'] = time();
        $_POST['updatetime'] = $_POST['createtime'];
        $_POST['userid'] = $module ? $userid : $_SESSION['userid'];
        $_POST['username'] = $module ? $username : $_SESSION['username'];

        $module = $module? $module : MODULE_NAME ;

        if (false === $model->create()) {
            $this->error($model->getError());
        }

        $_POST['id'] = $id = $model->add();

        if ($id !==false) {

            $catid = $module =='Page' ? $id : $_POST['catid'];

            if($_POST['aid']) {

                $Attachment =M('attachment');

                $aids = implode(',',$_POST['aid']);
                $data['id']=$id;
                $data['catid']= $catid;
                $data['status']= '1';
                $Attachment->where("aid in (".$aids.")")->save($data);
            }


            $data='';
            $cat = $this->Categorys[$catid];
            $url = geturl($cat,$_POST,$this->Urlrule);

            $data['id']= $id;
            $data['url']= $url[0];

            $model->save($data);

            $this->success(L('add_ok'));
        } else {
            $this->error(L('add_error').': '.$model->getDbError());
        }
    }
}