<?php

class IndexAction extends PublicAction
{

    public function index()
    {
        $this->assign('isIndex',1);
        $this->display();
    }
}
