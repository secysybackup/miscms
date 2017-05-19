<?php

class PosidAction extends PublicAction
{

    function index(){
        $posid_db = D('posid');
        $list = $posid_db->select();

        $this->assign('list', $list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    function add()
    {
        if (IS_POST) {
            $posid_db = D('posid');

            if(!$posid_db->create()){
                $this->error();
            }

            if ($posid_db->add()) {
                savecache('Posid');
                $this->success('新增成功!');
            } else {
                $this->error('新增失败!');
            }
            exit;
        }

        $this->display();
    }

    function edit()
    {
        $posid_db = D('posid');
        if (IS_POST) {

            if (false === $posid_db->create()) {
                $this->error($posid_db->getError());
            }

            if (false !== $posid_db->save()) {
                savecache('Posid');
                $this->success('修改成功');
            } else {
                $this->success(L('edit_error').': '.$posid_db->getDbError());
            }
            exit;
        }
        $id = I('get.id',0,'intval');
        if (!$id) {
            $this->error('缺少参数!');
        }

        $data = $posid_db->find($id);

        $this->assign('vo', $data);
        $this->display();
    }

    /**
     * 删除
     *
     */
    function delete()
    {
        $posid_db = M('Posid');
        $id = I('get.id', 0, 'intval');

        if ($id) {
            if (false !== $posid_db->delete($id)) {
                savecache('Posid');

                $this->success(L('delete_ok'));
            } else {
                $this->error(L('delete_error').': '.$posid_db->getDbError());
            }
        } else {
            $this->error (L('do_empty'));
        }
    }
}