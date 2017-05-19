<?php

class EmailAction extends PublicAction
{

    /**
     * 列表
     *
     */
    public function index()
    {
        $email_db = M('Email');

        //取得满足条件的记录总数
        $count = $email_db->count('id');

        if ($count > 0) {
            import("@.ORG.Page");

            $p = new Page($count, 15);

            //分页查询数据
            $voList = $email_db->limit($p->firstRow . ',' . $p->listRows)->select ( );

            $map[C('VAR_PAGE')] = '{$page}';

            $p->urlrule = U('Email/index', $map);

            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('page', $page);
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    function delete()
    {
        $email_db = M('Email');
        $id = I('get.id', 0 ,'intval');

        if(isset($id)) {
            if(false !== $email_db->delete($id)){
                $this->success(L('delete_ok'));
            }else{
                $this->error(L('delete_error').': '.$email_db->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }
}