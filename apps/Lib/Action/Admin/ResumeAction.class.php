<?php

class ResumeAction extends PublicAction
{

    /**
     * 列表
     *
     */
    public function index()
    {
        $listRows = 15;

        $model = M('Resume');
        $map = array();

        //取得满足条件的记录总数
        $count = $model->where($map)->count('id');

        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (! empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            }

            $p = new Page($count, $listRows);

            //分页查询数据
            $list = $model->where($map)->limit($p->firstRow . ',' . $p->listRows)->order('id desc')->select ( );

            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $list );
            $this->assign('page', $page );
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }



    public function detail()
    {
        $model = M('Resume');

        $id = I('id',0,'intval');

        $data = $model->getById($id);
        $this->assign('user', $data);

        $this->display();

    }

    function delete()
    {
        $db = M('Resume');
        $id = I('id');
        if (!$id) {
            $this->error('参数不正确！');
        }

        if(false !== $db->delete($id)){
            $this->success('删除成功!');
        }else{
            $this->error('删除失败: '.$db->getDbError());
        }

    }

}