<?php

class BlockAction extends PublicAction
{

    public function index()
    {
        $block_db = M('Block');

        $map['lang'] = array('eq',LANG_ID);

        $this->assign($_REQUEST);

        //取得满足条件的记录总数
        $count = $block_db->where($map)->count('id');

        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (! empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = 15;
            }

            $p = new Page($count, $listRows);

            //分页查询数据
            $voList = $block_db->where($map)->limit($p->firstRow . ',' . $p->listRows)->select ( );

            //分页跳转的时候保证查询条件
            foreach ( $map as $key => $val ) {
                if (! is_array ( $val )) {
                    $p->parameter .= "$key=" . urlencode ( $val ) . "&";
                }
            }
            $map[C('VAR_PAGE')]='{$page}';

            $p->urlrule = U('Block/index', $map);
            //分页显示
            $page = $p->show();

            //模板赋值显示
            $this->assign('list', $voList );
            $this->assign('page', $page );
        }

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }


    public function edit()
    {
        $block_db = M('Block');
        if (IS_POST) {

            if (false === $block_db->create()) {
                $this->error($block_db->getError());
            }

            if (false !== $block_db->save()) {
                attach_update('block-' .$_POST['id']);
                $this->success(L('edit_ok'));
            } else {
                $this->success (L('edit_error').': '.$block_db->getDbError());
            }
        } else {
            $id = $_GET['id'];
            $vo = $block_db->getById( $id );

            $this->assign('vo', $vo);

            attach_update_start();

            $this->display();
        }
    }


    function add()
    {
        if (IS_POST) {
            $block_db = D('Block');

            $_POST['lang'] = LANG_ID;
            if (false === $block_db->create()) {
                $this->error($block_db->getError());
            }

            $id = $block_db->add();

            if ($id !==false) {
                attach_update('block-' .$id);
                $jumpUrl = U('Block/index');

                $this->assign('jumpUrl',$jumpUrl);
                $this->success(L('add_ok'));
            } else {
                $this->error(L('add_error').': '.$block_db->getDbError());
            }
        } else {
            attach_update_start();
            $this->display();
        }
    }

    function delete()
    {
        $block_db = M('Block');
        $id = $_GET['id'];

        if (isset( $id )) {

            if(false !== $block_db->delete($id)){
                attach_delete('block-'.$id);
                $this->success('删除成功！');
            }else{
                $this->error('删除出错: '.$block_db->getDbError());
            }
        }else{
            $this->error('缺少参数');
        }
    }
}