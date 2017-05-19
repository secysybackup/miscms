<?php

class VcardModel extends Model
{

    /*
     * 表单验证
     */
    protected  $_validate = array(
        array('name','require','姓名不能为空！',1,'regex',1),
        array('email','email','邮箱格式不对!'),
    );


    /*
     * 自动完成
     */
    protected $_auto = array(
        array('createtime','time',1,'function'),
        array('updatetime','time',2,'function'),
    );
}