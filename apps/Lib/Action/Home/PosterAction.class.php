<?php

class PosterAction extends Action
{

    public function message() {

        $db = M('PosterMessage');

        $_POST['addtime'] = time();

        if (empty($_POST['username'])) {
            $this->error('你的姓名不能为空！');
        }
        if (empty($_POST['phone'])) {
            $this->error('您的手机号码不能为空！');
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