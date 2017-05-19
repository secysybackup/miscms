<?php

class ShippingAction extends PublicAction
{

    protected  $db,$fields;

    /**
     * 列表
     *
     */
    public function index()
    {
        $model = M('Shipping');

        $list = $model->order('listorder asc,id asc')->select( );

        $this->assign('list', $list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }


    public function add()
    {
        $model = D('Shipping');

        if (IS_POST) {

            $_POST['createtime'] = time();
            $_POST['updatetime'] = $_POST['createtime'];

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            if ($model->add()) {
                $this->success('添加成功！');
            } else {
                $this->error('添加失败: ' . $model->getDbError());
            }
        } else {
            $this->display();
        }
    }


    public function edit()
    {
        $model = D('Shipping');

        if (IS_POST) {

            if (false === $model->create()) {
                $this->error($model->getError());
            }

            // 更新数据
            if ($model->save()) {
                $this->success('添加成功！');
            } else {
                //错误提示
                $this->error('添加失败: ' . $model->getDbError());
            }
        } else {
            $id = $_REQUEST['id'];

            $vo = $model->getById($id);
            $this->assign('vo', $vo);

            $this->display();
        }
    }

    function delete()
    {
        $model = M('Shipping');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false!==$model->delete($id)){
                $this->success(L('delete_ok'));
            }else{
                $this->error(L('delete_error').': '.$model->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }

    /*状态*/
    public function status()
    {
        $model = D('Shipping');

        if($model->save($_GET)){
            $this->success(L('do_ok'));
        }else{
            $this->error(L('do_error'));
        }
    }

    public function listorder()
    {
        $model = M('Shipping');
        $ids = $_POST['listorders'];

        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $model->where('id='.$key)->save($data);
        }

        $this->success('排序成功!');
    }
}