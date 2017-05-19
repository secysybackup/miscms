<?php

class MemberGroupAction extends PublicAction
{

    function index()
    {
        $list = M('MemberGroup')->select();

        foreach($list as $key=>$val){
            $list[$key]['amount'] = M('Member')->where('groupid='.$val['id'])->count('id');
        }

        $this->assign('list', $list);
        $this->display();
    }

    function add()
    {
        if (IS_POST) {
            $_POST['status']=1;
            $model = D ('MemberGroup');

            if (false === $model->create ()) {
                $this->error ( $model->getError () );
            }

            $typeid = $model->add() ;

            if($typeid) {

                if(empty($_POST['keyid'])){
                    $data['typeid'] = $data['keyid'] = $typeid;
                    $model->save($data);
                }

                savecache('MemberGroup');
                $this->assign ( 'jumpUrl', U(MODULE_NAME.'/index') );
                $this->success (L('add_ok'));

            } else {
                $this->error (L('add_error').': '.$model->getDbError());
            }
        } else {
            $parentid = intval($_GET['parentid']);
            $keyid = intval($_GET['keyid']);
            $this->assign('keyid', $keyid);
            $array=array();

            if($parentid){

                foreach((array)$this->Type as $key => $r) {
                    if($r['keyid']!=$keyid || empty($r['status'])) continue;
                    $r['id']=$r['typeid'];
                    $array[] = $r;
                }

                import('@.ORG.Tree');
                $str = "<option value='\$typeid' \$selected>\$spacer \$name</option>";

                $tree = new Tree ($array);
                $select_type = $tree->get_tree(0, $str,$parentid);
                $this->assign('select_type', $select_type);
            }
            $this->display();
        }
    }


    function edit()
    {
        $model = M('MemberGroup');

        if (IS_POST) {
            $model = D('MemberGroup');

            if (false === $model->create()) {
                $this->error( $model->getError() );
            }

            if ($model->save()) {
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
        } else {
            $id = $_GET['id'];

            $vo = $model->find($id);

            $this->assign('vo', $vo);
            $this->display();
        }
    }

    function delete()
    {
        $db = M('MemberGroup');
        $id = I('get.id');
        if (!$id) {
            $this->error('参数不正确！');
        }
        $count = M('Member')->where('groupid='.$id)->count('id');
        if ($count) {
            $this->error('该会员组下有会员，不能删除！');
        }
        if(false !== $db->delete($id)){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败: '.$db->getDbError());
        }
    }
}