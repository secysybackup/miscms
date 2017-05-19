<?php

class PosterModel extends Model
{

    /*
     * 表单验证
     */
    protected  $_validate = array(
        array('appname','require','标题不能为空！',1,'regex',1),
    );


    /*
     * 自动完成
     */
    protected $_auto = array(
        array('createtime','time',1,'function'),
        array('updatetime','time',2,'function'),
    );
}