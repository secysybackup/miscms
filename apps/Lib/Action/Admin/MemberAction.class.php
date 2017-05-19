<?php

class MemberAction extends PublicAction
{

    public $memberGroup;

    function _initialize()
    {
        parent::_initialize();
        $this->memberGroup = M('MemberGroup')->select();

        $this->assign('membergroup',$this->memberGroup);
    }


    //会员设置
    public function config()
    {
        $config_db = M('MemberConfig');
        if (IS_POST) {

            $sta = false;
            foreach($_POST as $key=>$value){
                $data['value'] = $value;
                $f = $config_db->where("varname='".$key."'")->save($data);
                if ($f) {
                    $sta = true;
                }
            }

            if($sta){
                $this->success('保存成功!');
            }else{
                $this->error('没有发生更改!');
            }
        }
        $config = $config_db->select();

        $this->assign('member_config',$config);
        $this->display();
    }

    function index()
    {
        import('@.ORG.Page');

        $keyword    = $_GET['keyword'];
        $searchtype = $_GET['searchtype'];
        $groupid    = intval($_GET['groupid']);

        $this->assign($_GET);

        if (!empty($keyword) && !empty($searchtype)) {
          $where[$searchtype]=array('like','%'.$keyword.'%');
        }
        if ($groupid) {
            $where['groupid']=$groupid;
        }

        $member_db = D('Member');
        $count = $member_db->where($where)->count();
        $page = new Page($count,20);
        $show = $page->show();
        $this->assign("page",$show);

        $list = $member_db->relation('MemberGroup')->order('id')->where($where)
        ->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('ulist',$list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    /**
     * 更新
     *
     */
    function edit()
    {
        $member_db = D('Member');
        if (IS_POST) {
            $_POST['password'] = $_POST['password'] ? sysmd5($_POST['password']) : $_POST['opassword'];

            if($data = $member_db->create()){
                if (!empty($data['id'])) {
                    if (false !== $member_db->save()) {
                        $this->redirect('index');
                    } else {
                        $this->error(L('edit_error').$member_db->getDbError());
                    }
                } else {
                    $this->error(L('do_error'));
                }
            } else {
                $this->error($member_db->getError());
            }
        } else {
            $id = $_GET['id'];

            if(empty($id))
                $this->error(L('do_empty'));

            $member = $member_db->find($id);


            $this->assign('member', $member);

            $this->display();
        }

    }


    function add()
    {
        if (IS_POST) {
            $model_db = D('Member');

            if ($data = $model_db->create()) {
                if(false!==$model_db->add()){
                    $this->success(L('add_ok'));
                } else {
                    $this->error(L('add_error'));
                }
            }else{
                $this->error($model_db->getError());
            }
        } else {
            $this->display();
        }
    }

    function delete()
    {
        $db = M('Member');
        $id = I('get.id');
        if (!$id) {
            $this->error('参数不正确！');
        }

        if(false !== $db->delete($id)){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败: '.$db->getDbError());
        }
    }
}