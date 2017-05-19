<?php

class MenuAction extends PublicAction
{

    public function index()
    {
        //搜索
        $keyword = I('keyword', '', 'string');
        $condition = array('like','%'.$keyword.'%');
        $map['id|name'] = array($condition, $condition, '_multi'=>true); //搜索条件

        //获取所有菜单
        $map['status'] = array('egt', '0'); //禁用和正常状态

        $data_list = D('Menu')->where($map)->order('listorder asc, id asc')->select();

        import('@.ORG.Tree');
        $tree = new Tree();
        $data_list = $tree->toFormatTree($data_list,'name','id','parentid');

        $this->assign('menu_list', $data_list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    // 添加菜单
    function add()
    {
        $menu_db = D('Menu');
        if (IS_POST) {

            if (false === $menu_db->create()) {
                $this->error($menu_db->getError());
            }

            $pid = I('get.parentid', 0, 'intval');
            $id = $menu_db->add();

            if ($id !==false) {

                savecache('Menu');

                $this->assign('jumpUrl', U('Menu/index','pid='.$pid));
                $this->success(L('add_ok'));
            } else {
                $this->error (L('add_error').': '.$menu_db->getDbError());
            }
        } else {
            $parentid = I('get.parentid', 0, 'intval');

            import('@.ORG.Tree');
            $result = $menu_db->select();
            foreach($result as $r) {
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }

            $str  = "<option value='\$id' \$selected>\$spacer \$name</option>";

            $tree = new Tree($array);
            $tree->icon = array('│ &nbsp;&nbsp;&nbsp;', '├─ ', '└─');
            $select_categorys = $tree->get_tree(0, $str,$parentid);
            $this->assign('select_categorys', $select_categorys);
            $this->display();
        }

    }


    // 编辑菜单
    function edit()
    {
        $menu_db = D('Menu');
        if (IS_POST) {

            if (false === $menu_db->create()) {
                $this->error($menu_db->getError());
            }

            if (false !== $menu_db->save()) {

                savecache('Menu');

                $this->success('修改成功');
            } else {
                $this->success(L('edit_error').': '.$menu_db->getDbError());
            }
        } else {
            $id = I('get.id', 0, 'intval');

            $vo = $menu_db->find($id);
            $parentid = $vo['parentid'];
            import('@.ORG.Tree');
            $result = $this->Menu;
            foreach ($result as $r) {
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }
            $str  = "<option value='\$id' \$selected>\$spacer \$name</option>";
            $tree = new Tree($array);
            $tree->icon = array('│ &nbsp;&nbsp;&nbsp;', '├─ ', '└─');
            $select_categorys = $tree->get_tree(0, $str,$parentid);
            $this->assign('select_categorys', $select_categorys);

            $this->assign('vo', $vo);

            $this->display();
        }
    }

    /**
     * 删除
     *
     */
    function delete()
    {
        $menu_db = M('Menu');
        $id = I('get.id', 0, 'intval');

        if ($id) {
            $strChildId = $this->getStrChildId($id);
            if (false !== $menu_db->delete($strChildId)) {
                savecache('Menu');

                $this->success('删除成功！');
            } else {
                $this->error('删除失败！: '.$menu_db->getDbError());
            }
        } else {
            $this->error (L('do_empty'));
        }
    }

    /**
     * 批量删除
     *
     */
    function deleteall()
    {
        $menu_db = M('Menu');
        $ids = $_POST['ids'];

        if (!empty($ids) && is_array($ids)) {

            $id = implode(',',$ids);
            if (false !== $menu_db->delete($id)) {
                savecache('Menu');
                $this->success(L('delete_ok'));
            } else {
                $this->error(L('delete_error').': '.$menu_db->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }


    //获取当前菜单id和子栏目id
    function getStrChildId($id)
    {
        $menu_db = M('Menu');
        $strChildId = $id;
        $list = $menu_db->where('parentid='.$id)->select();
        if (!empty($list)) {
            foreach ($list as $val) {
                $strChildId = $strChildId.','.$this->getStrChildId($val['id']);
            }
        }

        return $strChildId;
    }
}