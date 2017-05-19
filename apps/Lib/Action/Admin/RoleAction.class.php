<?php

class RoleAction extends PublicAction
{

    public $usergroup;

    function _initialize()
    {
        parent::_initialize();

        $data = M('RoleUser')->select();

        $_SESSION['role'] = array();
        if($_SESSION['role'] == 1){
            $_SESSION['role'] = $data;
        } else {
            foreach($data as $k=>$v){

                if($v['id'] !=1)
                    $_SESSION['role'][] = $v;
            }
        }
    }
    /* ========角色部分======== */

    // 角色管理列表
    public function index()
    {
        $RoleDB = D('Role');
        $list = $RoleDB->getAllRole();
        $this->assign('list',$list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    // 添加角色
    public function add()
    {
        $RoleDB = D("Role");
        if(isset($_POST['dosubmit'])) {
            //根据表单提交的POST数据创建数据对象
            if($RoleDB->create()){
                if($RoleDB->add()){
                    $this->assign("jumpUrl",U('/Admin/User/role'));
                    $this->success('添加成功！');
                }else{
                    $this->error('添加失败!');
                }
            }else{
                $this->error($RoleDB->getError());
            }
        }else{
            $this->assign('tpltitle','添加');
            $this->display();
        }
    }

    // 编辑角色
    public function edit()
    {
        $RoleDB = D("Role");
        if(IS_POST) {
            //根据表单提交的POST数据创建数据对象
            if($RoleDB->create()){
                if($RoleDB->save()){
                    $this->assign("jumpUrl",U('/Admin/User/role'));
                    $this->success('编辑成功！');
                }else{
                    $this->error('编辑失败!');
                }
            }else{
                $this->error($RoleDB->getError());
            }
        }else{
            $id = $this->_get('id','intval',0);
            if(!$id)$this->error('参数错误!');
            $info = $RoleDB->getRole(array('id'=>$id));
            $this->assign('tpltitle','编辑');
            $this->assign('info',$info);
            $this->display();
        }
    }

    //删除角色
    public function delete()
    {
        $id = I('id',0,'intval');
        if(!$id)$this->error('参数错误!');

        if ($id == 1) {
            $this->error('超级管理不允许删除!');
        }
        $RoleDB = D('Role');
        if($RoleDB->delRole('id='.$id)){
            $this->assign("jumpUrl",U('Role/index'));
            $this->success('删除成功！');
        }else{
            $this->error('删除失败!');
        }
    }

    // 排序权重更新
    public function sort()
    {
        $sorts = $this->_POST('sort');
        if(!is_array($sorts))$this->error('参数错误!');
        foreach ($sorts as $id => $sort) {
            D('Role')->upRole( array('id' =>$id , 'sort' =>intval($sort) ) );
        }
        $this->assign("jumpUrl",U('Role/index'));
        $this->success('更新完成！');
    }

    /* ========权限设置部分======== */

    //权限浏览
    public function access()
    {
        $model = M('Access');
        $rid = intval($_GET['roleid']);
        $alist = $model->where('role_id = '.$rid)->getField('node_id,role_id');
        $node = M('Node');

        $r = $node->where("pid=0 and status=1")->select();

        $this->assign('topnode', $r);

        $groups[0] = array('id'=>0,'name'=>L('ACCESS_PUBLIC'));
        foreach($this->Menu as $key=>$r){
            if($r['parentid']==0)
                $groups[$r[id]]=$r;
        }

        $this->assign('groups', $groups);

        foreach($groups as $key=>$res){
            $result = $node->where("groupid=$res[id] and status=1")->select();
            $array = array();
            foreach($result as $r) {
                $r['parentid'] = $r['pid'];
                $r['selected'] = array_key_exists($r['id'],$alist)   ? 'checked' : '';
                $array[] = $r;
            }
            $nodes[$res['id']]['data']  =$array;
            $nodes[$res['id']]['groupinfo']=$res;
        }

        $node_app = $model->where("pid=0 and status=1")->select();

        $this->assign('node_app', $node_app);

        //记录当前位置
        cookie('location', $_SERVER['REQUEST_URI']);

        $this->assign('alist', $alist);
        $this->assign('node', $nodes);
        $this->assign('rid', $rid);
        $this->display();
    }

    //权限编辑
    public function access_edit()
    {
        $model = M('Access');
        $rid = $_POST['rid'];
        $nid = $_POST['nid'];

        if (!empty($rid)) {
            if ($nid) {

                $node_id = implode(',', $nid);
                $node = M('Node');
                $list = $node->where('id in(' . $node_id . ')')->select();
                $model->where('role_id = ' . $rid)->delete();
                foreach ($list as $key => $node) {
                    $data[$key]['role_id'] = $rid;
                    $data[$key]['node_id'] = $node['id'];
                    $data[$key]["level"] = $node['level'];
                    $data[$key]["pid"] = $node['pid'];
                }

                $r = $model->addAll($data);

            } else {

                $r = $model->where('role_id = ' . $rid)->delete();
            }
            if (false !== $r) {
                $this->success(L('role_ok'));
            } else {

                $this->error(L('role_error'));
            }

        } else {
            $this->error(L('do_empty'));
        }
    }

}