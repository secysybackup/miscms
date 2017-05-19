<?php

class HotpageAction extends PublicAction
{

    public function message() {
        //获取路由规则
        $db = M('HotpageMessage');

        $_POST['addtime'] = time();

        if (empty($_POST['username'])) {
            $this->error('你的称谓不能为空！');
        }
        if (empty($_POST['contact'])) {
            $this->error('您的联系方式不能为空！');
        }
        if (empty($_POST['content'])) {
            $this->error('您的需求信息不能为空！');
        }
        if (false === $db->create()) {
            $this->error($db->getError());
        }
        $id = $db->add();

        if ($id !==false) {
            $this->success('提交成功！');
        } else {
            $this->error('提交失败！');
        }
    }
}