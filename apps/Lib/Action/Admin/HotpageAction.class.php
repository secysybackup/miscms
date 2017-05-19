<?php

class HotpageAction extends PublicAction
{
    public function index()
    {
        $list = M('Hotpage')->select();
        $this->assign('list', $list);
        $this->assign($this->SysConfig);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    public function add()
    {
        if (IS_POST) {
            $model = D('Hotpage');

            if (empty($_POST['title'])){
                $this->error('名称不能为空！');
            }

            $_POST['createtime'] = time();
            $_POST['updatetime'] = time();
            if (false === $model->create()) {
                $this->error($model->getError());
            }

            if (false !== $model->add()) {
                $this->success('添加成功！');
            }
        } else {
            $this->display();
        }

    }

    public function edit()
    {
        $model = D('Hotpage');
        if (IS_POST) {

            if (empty($_POST['title'])){
                $this->error('标题不能为空！');
            }

            $_POST['updatetime'] = time();

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            if (false !== $model->save()) {
                $this->success('修改成功！');
            }
        } else {
            $id = I('get.id');
            $data = M('Hotpage')->find($id);
            $data['content'] = htmlspecialchars($data['content']);

            $this->assign('data', $data);
            $this->display();
        }

    }

    function message()
    {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('缺少参数！');
        }
        $list = M("HotpageMessage")->where('hotpage_id='.$id)->select();

        $this->assign('list', $list);
        $this->display();
    }

    function deletemsg()
    {
        $id = $_GET['id'];
        $hotpagemessage_db = D('HotpageMessage');
        if (false !== $hotpagemessage_db->delete($id)) {
            $this->success(L('delete_ok'));
        } else {
            $this->error(L('delete_error').$hotpagemessage_db->getDbError());
        }
    }


    function delete()
    {
        $id = $_GET['id'];
        $hotpage_db = D('Hotpage');
        if (false !== $hotpage_db->delete($id)) {
            $this->success(L('delete_ok'));
        } else {
            $this->error(L('delete_error').$hotpage_db->getDbError());
        }
    }

    function html()
    {
        $id = I('get.id');

        $model = M('Hotpage');

        $list = $model->find($id);
        $this->assign('list', $list);

        $this->assign($this->SysConfig);

        $this->buildHtml($id,'./hot/','./hot/i.php');

        $this->success('生成成功！');
    }
}