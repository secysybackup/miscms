<?php

class ResumeModel extends Model
{

    /*
     * 表单验证
     */
    protected  $_validate = array(
        array('username','require','用户名不能为空！',1,'regex',1),
        array('sex','require','性别不能为空！',1,'regex',1),
        array('birthday','require','出生年月不能为空！',1,'regex',1),
        array('phone','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！',0,'regex',1),
        array('position','require','应聘职位不能为空！',0,'regex',1),
        array('email','require','邮箱不能为空！',1,'regex',3),
        array('email','email','邮箱格式不对!'),
    );


    /*
     * 自动完成
     */
    protected $_auto = array(
        array('createtime','time',1,'function'),
        array('updatetime','time',2,'function'),
        array('reg_ip','get_client_ip',1,'function'),
    );
}