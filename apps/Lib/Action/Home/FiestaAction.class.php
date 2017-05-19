<?php

class FiestaAction extends PublicAction
{
    public function index()
    {
        $today =  strtotime(date('Y-m-d'));

        $data = M('Fiesta')->where('createtime='.$today)->find();

        if ($data) {
            $this->assign('fiesta',$data);
            $this->display();
        } else {
            $this->ajaxReturn(0);
        }

    }
}