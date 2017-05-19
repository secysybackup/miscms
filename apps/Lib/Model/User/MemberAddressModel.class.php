<?php

class MemberAddressModel extends Model
{
    /*
     * 表单验证
     */
    protected  $_validate = array(
        array('consignee','require','收货人姓名不能为空！',1,'regex',1),
        array('zipcode','require','邮政编码不能为空！',1,'regex',1),
        array('mobile','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！',0,'regex',1),
    );
}