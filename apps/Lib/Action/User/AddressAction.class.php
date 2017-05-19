<?php

class AddressAction extends PublicAction
{

    protected $userid;

    function _initialize()
    {
        parent::_initialize();
        //检测用户是否登陆
        $this->checkLogin();
        $this->userid = $_SESSION['member']['id'];
        $member_db = M('Member');
        $user = $member_db->find($this->userid);
        $this->assign('user',$user);
    }

    function index()
    {
        $address_db = D('MemberAddress');

        $area = M('Area')->getField('id,name');
        $this->assign('area', $area);

        $list =  $address_db->where('userid='.$this->userid)->select();
        $this->assign('list', $list);
        $this->display();
    }

    function add(){
        if (IS_POST) {
            $address_db = D('MemberAddress');
            $_POST['userid'] = $this->userid;

            if (!$address_db->create()) {
                $this->error($address_db->getError());
            }
            $result = $address_db->add();
            if(false !== $result) {
                if (!empty($_POST['isdefault'])) {
                    $address_db->where('id!='.$_POST['id'])->save(array('isdefault'=>0));
                }
                $this->success('新增成功！');
            }else{
                $this->error('新增失败！');
            }
        } else {
            $this->display();
        }
    }

    public function edit()
    {
        $model = D('MemberAddress');

        if (IS_POST) {

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            // 更新数据
            $list = $model->save();

            if (false !== $list) {
                if (!empty($_POST['isdefault'])) {
                    $model->where('id!='.$_POST['id'])->save(array('isdefault'=>0));
                }
                $this->success('保存成功！');
            } else {
                //错误提示
                $this->error('保存失败: '.$model->getDbError());
            }
        } else {
            $id = $_REQUEST['id'];
            $adr = $model->getById($id);

            $this->assign('adr', $adr);
            $this->display();
        }
    }


    function delete()
    {
        $model = M('MemberAddress');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false !== $model->delete($id)){
                $this->success('删除成功！');
            }else{
                $this->error('删除失败: '.$model->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }
}