<?php

class FiestaAction extends PublicAction
{

    /**
     * 默认列表页
     */
    public function index()
    {
        $fiesta_db = M('Fiesta');

        //取得满足条件的记录总数
        $count = $fiesta_db->count('id');

        if ($count > 0) {
            $listRows = C('PAGE_LISTROWS');
            import("@.ORG.Page");
            //创建分页对象
            $p = new Page($count, $listRows);

            //分页查询数据
            $list = $fiesta_db->limit($p->firstRow . ',' . $p->listRows)->select( );

            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $list);
            $this->assign('page', $page);
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 新增
     */
    public function add()
    {
        if (IS_POST) {
            $model = M('Fiesta');
            if (empty($_POST['title'])){
                $this->error('名称不能为空！');
            }
            if (empty($_POST['createtime'])){
                $this->error('时间不能为空！');
            }

            $_POST['createtime'] = strtotime($_POST['createtime']);
            if (false === $model->create()) {
                $this->error($model->getError());
            }

            $_POST['id'] = $id= $model->add();

            if ($id !==false) {

                attach_update('fiesta-'.$_POST['id']);

                $this->assign('jumpUrl', U('Kefu/index') );

                $this->success(L('add_ok'));
            } else {
                $this->error(L('add_error').': '.$model->getDbError());
            }
        }
        attach_update_start();
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $model = M('Fiesta');
        if (IS_POST) {
            if (empty($_POST['title'])){
                $this->error('名称不能为空！');
            }
            if (empty($_POST['createtime'])){
                $this->error('时间不能为空！');
            }

            $_POST['createtime'] = strtotime($_POST['createtime']);

            if(empty($_POST))
                $this->error (L('do_empty'));

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            // 更新数据
            $list = $model->save();

            if (false !== $list) {

                attach_update('fiesta-'.$_POST['id']);

                $this->success(L('edit_ok'));

            } else {
                //错误提示
                $this->error(L('edit_error').': '.$model->getDbError());
            }
        } else {
            $id = I('get.id', 0, 'intval');

            $r = $model->find($id);

            attach_update_start();
            $this->assign('vo',$r);
            $this->display();
        }
    }

    function delete()
    {
        $model = M('Fiesta');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false!==$model->delete($id)){
                attach_delete('fiesta-'.$id);
                $this->success(L('delete_ok'));
            }else{
                $this->error(L('delete_error').': '.$model->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }
}