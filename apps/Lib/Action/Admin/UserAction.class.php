<?php

class UserAction extends PublicAction
{

    function _initialize()
    {
        parent::_initialize();

        $data = M('RoleUser')->select();

        $_SESSION['role'] = $data;
    }

    //用户列表
    public function index()
    {
        import('ORG.Util.Page');// 导入分页类
        $role = M('Role')->getField('id,name');
        $map = array();

        $keyword = I('keyword', '', 'string');
        $condition = array('like','%'.$keyword.'%');
        $map['id|username'] = array($condition, $condition, '_multi'=>true); //搜索条件

        $default_roleid = 0;
        if ($_SESSION['admin']['role']>1) {
            $default_roleid = $_SESSION['admin']['role'];
        }
        $roleid = I('roleid', $default_roleid, 'intval');
        if ($roleid) {
            $map['role'] = $roleid;
        }

        $UserDB = D('User');
        $count = $UserDB->where($map)->count();
        $Page = new Page($count);// 实例化分页类 传入总记录数
        // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        $nowPage = isset($_GET['p'])?$_GET['p']:1;
        $show = $Page->show();// 分页显示输出
        $list = $UserDB->where($map)->order('id ASC')->page($nowPage.',5')->select();
        $this->assign('role',$role);
        $this->assign('list',$list);
        $this->assign('page',$show);// 赋值分页输出

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    // 添加用户
    public function add()
    {
        $UserDB = D("User");
        if(IS_POST) {

            $password = $_POST['password'];
            $repassword = $_POST['repassword'];
            if(empty($password) || empty($repassword)){
                $this->error('密码必须！');
            }
            if($password != $repassword){
                $this->error('两次输入密码不一致！');
            }

            //根据表单提交的POST数据创建数据对象
            if($UserDB->create()){
                $user_id = $UserDB->add();
                if($user_id){
                    $data['user_id'] = $user_id;
                    $data['role_id'] = $_POST['role'];
                    if (M("RoleUser")->data($data)->add()){
                        $this->assign("jumpUrl",U('/Admin/User/index'));
                        $this->success('添加成功！');
                    }else{
                        $this->error('用户添加成功,但角色对应关系添加失败!');
                    }
                }else{
                    $this->error('添加失败!');
                }
            }else{
                $this->error($UserDB->getError());
            }
        }else{
            $role = D('Role')->getAllRole(array('status'=>1),'sort DESC');
            $this->assign('role',$role);
            $this->assign('tpltitle','添加');
            $this->display();
        }
    }

    // 编辑用户
    public function edit()
    {
        $UserDB = D("User");
        if(IS_POST) {
            $password = $_POST['password'];
            $repassword = $_POST['repassword'];
            if(!empty($password) || !empty($repassword)){
                if($password != $repassword){
                    $this->error('两次输入密码不一致！');
                }
                $_POST['password'] = sysmd5($password);
            }

            if(empty($password) && empty($repassword)) unset($_POST['password']);//不填写密码不修改

            //根据表单提交的POST数据创建数据对象
            if($UserDB->create()){
                if($UserDB->save()){
                    $where['user_id'] = $_POST['id'];
                    $data['role_id'] = $_POST['role'];
                    M("RoleUser")->where($where)->save($data);
                    $this->assign("jumpUrl",U('/Admin/User/index'));
                    $this->success('编辑成功！');
                }
            }else{
                $this->error($UserDB->getError());
            }
        }else{
            $id = I('get.id');
            $role = D('Role')->getAllRole(array('status'=>1),'sort DESC');
            $info = $UserDB->getUser(array('id'=>$id));
            $this->assign('role',$role);
            $this->assign('info',$info);
            $this->display();
        }
    }

    //ajax 验证用户名
    public function check_username()
    {
        $userid = $this->_get('userid');
        $username = $this->_get('username');
        if(D("User")->check_name($username,$userid)){
            echo 1;
        }else{
            echo 0;
        }
    }

    //删除用户
    public function delete()
    {
        $id = I('id',0,'intval');
        if(!$id)$this->error('参数错误!');
        $UserDB = D('User');
        if($id== 1){     //无视系统权限的那个用户不能删除
            $this->error('禁止删除此用户!');
        }
        if($UserDB->delUser('id='.$id)){
            if(M("RoleUser")->where('user_id='.$id)->delete()){
                $this->assign("jumpUrl",U('/Admin/User/index'));
                $this->success('删除成功！');
            }else{
                $this->error('用户成功,但角色对应关系删除失败!');
            }
        }else{
            $this->error('删除失败!');
        }
    }
}