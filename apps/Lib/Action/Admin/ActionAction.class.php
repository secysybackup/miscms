<?php

class ActionAction extends PublicAction
{

    public function actionLog()
    {
        //获取列表数据
        $aUid=I('get.uid',0,'intval');
        if($aUid) $map['user_id']=$aUid;
        $map['status']    =   array('gt', -1);
        $list   =   M('ActionLog')->order('id desc')->select();

        $data = M('Action')->select();
        foreach($data as $val){
            $action_list[$val['id']] = $val;
        }
        $this->assign('action_list', $action_list);
        if (count($list) > 100) {
            M('ActionLog')->where('create_time>'.$list[0]['create_time'])->delete();
        }
        $this->assign('_list', $list);
        $this->display();
    }

    public function detail($id = 0)
    {
        empty($id) && $this->error(L('_PARAMETER_ERROR_'));

        $info = M('ActionLog')->field(true)->find($id);

        $this->assign('info', $info);
        $this->meta_title = L('_CHECK_THE_BEHAVIOR_LOG_');
        $this->display();
    }

    public function remove($ids = 0)
    {
        empty($ids) && $this->error(L('_PARAMETER_ERROR_'));
        if(is_array($ids)){
            $map['id'] = array('in', $ids);
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('ActionLog')->where($map)->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

    /**
     * 清空日志
     */
    public function clear()
    {
        $res = M('ActionLog')->where('1=1')->delete();
        if($res !== false){
            $this->success(L('_LOG_EMPTY_SUCCESSFULLY_'));
        }else {
            $this->error(L('_LOG_EMPTY_'));
        }
    }

}
