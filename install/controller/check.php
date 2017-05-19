<?php
header("Content-type: text/html; charset=utf-8");
//判断是否已经安装过
if(file_exists("./install.lock")){
	die("网站已经安装过！" );
}


define('OB_PATH',str_replace('install','',str_replace('\\','/',dirname(__FILE__))));

$check_dir=array(WEB_ROOT."public");

view('check',array('check_dir'=>$check_dir));