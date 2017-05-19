<?php

class MemberModel extends Model
{
    /*
     * 表单验证
     */
    protected  $_validate = array(
        array('username','require','用户名不能为空！',1,'regex',1),
        array('username','','用户名已存在！',1,'unique',1),
        array('password','/^.{6,18}$/','密码长度不能小于6位！',0,'regex',1),
        array('tel','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！',0,'regex',1),
        array('email','require','邮箱不能为空！',1,'regex',3),
        array('email','email','邮箱格式不对!'),
        array('email','checkEmail','邮箱已经存在！',1,'callback',3),
    );


    /*
     * 自动完成
     */
    protected $_auto = array(
        array('password','sysmd5',1,'function'),
        array('reg_time','time',1,'function'),
        array('updatetime','time',2,'function'),
        array('reg_ip','get_client_ip',1,'function'),
    );

    function checkEmail(){
        $user = M('Member');
        if(empty($_POST['id'])){
            if($user->getByEmail($_POST['email'])){
                return false;
            }else{
                return true;
            }
        }else{
            //判断邮箱是否已经使用
            if($user->where("id!={$_POST['id']} and email='{$_POST['email']}'")->find()){
                return false;
            }else{
                return true;
            }
        }
    }

}