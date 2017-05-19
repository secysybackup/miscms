<?php
header("Content-type: text/html; charset=utf-8");

//判断是否已经安装过
if(file_exists("./install.lock")){
 die("网站已经安装过！" );
}


$cur_dir = dirname(__FILE__);
define('WEB_ROOT',substr($cur_dir,0,-7));
define('CONTROLLER_PATH','controller/');
define('MODEL_PATH','lib/');
define('VIEW_PATH','templates/');
define('DB_PATH','db/');
//include MODEL_PATH.'init.php'; //加载初始化页面

$s_name=str_replace('install','',dirname($_SERVER['SCRIPT_NAME']));
$cms_url='http://'.$_SERVER['HTTP_HOST'].$s_name;
define('WEB_URL',$cms_url);


$a = !empty($_GET['a'])?$_GET['a']:'index';

//define('CONTROLLER',$c);
//define('ACTION',$a);

function view($value,$data=array()){
	extract($data);
	include VIEW_PATH.$value.'.php';
  //include VIEW_PATH.'footer.php';
}

include(CONTROLLER_PATH.$a.'.php');