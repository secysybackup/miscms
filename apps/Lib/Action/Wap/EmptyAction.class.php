<?php

class EmptyAction extends Action
{

    public function _empty() {

        //空操作 空模块
        header("HTTP/1.0 404 Not Found");
        $this->display('/public/404.html');
    }
}