<?php

class SlideAction extends PublicAction
{

    /**
     * 默认操作
     *
     */
    public function index()
    {
        $slide = M('Slide');
        $list = $slide->select();
        $this->assign('list', $list);

        //记录当前位置
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display();
    }

    function add()
    {
        if (IS_POST) {
            $model = D('Slide');

            $_POST['lang'] = LANG_ID;
            if (false === $model->create ()) {
                $this->error( $model->getError () );
            }

            $id = $model->add();

            if ($id !==false) {

                $jumpUrl = U('Slide/index');

                $this->assign ( 'jumpUrl',$jumpUrl );
                $this->success (L('add_ok'));
            } else {
                $this->error (L('add_error').': '.$model->getDbError());
            }
        } else {
            $this->display();
        }
    }


    function edit()
    {
        $slide_db = D('Slide');
        if (IS_POST) {

            if (false === $slide_db->create()) {
                $this->error($slide_db->getError ());
            }

            if (false !== $slide_db->save ()) {
                $jumpUrl = $_POST['forward'] ? $_POST['forward'] : U(MODULE_NAME.'/index');

                $this->assign('jumpUrl', $jumpUrl);
                $this->success(L('edit_ok'));
            } else {
                $this->success (L('edit_error').': '.$slide_db->getDbError());
            }
        } else {
            $id = I('id',0 ,'intval');

            if(empty($id))
                $this->error(L('do_empty'));

            $vo = $slide_db->getById($id);

            $this->assign('vo', $vo);
            $this->display();
        }
    }

    function picmanage()
    {
        $fid = intval($_REQUEST['fid']);
        if(!$fid){
          $this->error(L('do_empty'));
        }

        $map = array();
        $map['lang'] = array('eq',LANG_ID);

        $slide = D('Slide')->find($fid);

        $map['fid'] = array('eq',$fid);
        $list = D('slide_data')->where($map)->order("listorder ASC ,id DESC ")->select();
        $this->assign('list', $list);
        $this->assign('fid', $fid);
        $this->assign('slide', $slide);

        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    function addpic()
    {
        if (IS_POST) {
            $_POST['lang'] = LANG_ID;

            $model = D('Slide_data');
            if (false === $model->create ()) {
                $this->error ( $model->getError () );
            }
            $_POST['id'] = $id= $model->add();
            if ($id !==false) {

                attach_update('slide-' .$_POST['id']);

                $this->assign ( 'jumpUrl', U('slide/picmanage?fid='.$_POST['fid']) );
                $this->success (L('add_ok'));
            } else {
                $this->error (L('add_error').': '.$model->getDbError());
            }
        } else {
            $fid = intval($_REQUEST['fid']);
            if(!$fid) $this->error(L('do_empty'));
            $map = array();

            $map['lang']=array('eq',LANG_ID);

            $slide = D('slide')->find($fid);
            $map['fid']=array('eq',$fid);
            $list = D('slide_data')->where($map)->order(" listorder ASC ,id DESC ")->select();

            $vo['status'] = 1;
            $this->assign('vo', $vo);
            $this->assign('list', $list );
            $this->assign('fid', $fid );
            $this->assign('slide', $slide );

            attach_update_start();
            $this->display();
        }
    }

    function editpic()
    {
        if (IS_POST) {
            $model = D('slide_data');
            if (false === $model->create ()) {
                $this->error($model->getError());
            }

            if (false !== $model->save ()) {

                attach_update('slide-' .$_POST['id']);

                $this->assign( 'jumpUrl', U('Slide/picmanage','&fid='.$_POST['fid']));
                $this->success(L('edit_ok'));
            } else {
                $this->success(L('edit_error').': '.$model->getDbError());
            }
        } else {
            $id=intval($_REQUEST['id']);
            $fid=intval($_REQUEST['fid']);
            if(!$id) $this->error(L('do_empty'));
            $slide = D('Slide')->find($fid);

            $vo = D('slide_data')->find($id);
            $this->assign('fid', $fid );
            $this->assign('vo', $vo );
            $this->assign('slide', $slide );

            attach_update_start();
            $this->display();
        }
    }

    function listorder()
    {
        $model = M ('slide_data');
        $pk = $model->getPk ();
        $ids = $_POST['listorders'];
        foreach($ids as $key=>$r) {
            $data['listorder']=$r;
            $model->where($pk .'='.$key)->save($data);
        }
        $this->success ('排序成功！');
    }


    function delete()
    {
        $slide_db = M('Slide');
        $id = $_REQUEST['id'];

        if (isset($id)) {
            if(false !== $slide_db->delete($id)){
                $model = M('slide_data');
                $data = $model->where("fid=".$id)->select();
                foreach($data as $val){
                    $model->delete($val['id']);
                    attach_delete('slide-'.$val['id']);
                }
                $this->success('删除成功！');
            }else{
                $this->error('删除失败: '.$slide_db->getDbError());
            }
        }else{
            $this->error (L('do_empty'));
        }
    }

    function deletepic()
    {
        $model = M('slide_data');
        $pk = $model->getPk();
        $id = $_REQUEST[$pk];

        if(isset($id)) {
            if(false!==$model->delete($id)){
                attach_delete('slide-'.$id);
                $this->success('删除成功！');
            }else{
                $this->error('删除失败: '.$model->getDbError());
            }
        }else{
            $this->error(L('do_empty'));
        }
    }
}