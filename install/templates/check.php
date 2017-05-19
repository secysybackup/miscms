<!DOCTYPE html>
<html>
<head>
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>WisCms系统安装程序</title>
	<script src="../public/admin/js/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/zcut.css" />
</head>

<body class="bodyImg">
<div class="warp">
	<div class="miHead bgImg">
		<span class="vn fr">WisCms企业网站管理系统&nbsp;5.8&nbsp;&nbsp;全新安装</span>
	</div>
	<div class="miBody">
		<div class="step">
			<div class="box">
				<span class="num bgImg s1_on fl"></span>
				<span class="num bgImg s2_off fl"></span>
				<span class="num bgImg s3_off mrn fl"></span>
			</div>
		</div>
		<div class="test">
			<div class="box">
				<h4>目录权限</h4>
				<?php
					$check=0;
					foreach($check_dir as $k=>$v){
				?>
				<span class="wd">【<?php echo $v;?>】文件夹</span>
				<?php if(is_writable($v)) {
					 echo "<span>可写</span>";
					} else {
						echo "<span style=\"color:red\">不可写</span>";$check=1;
				}?>
				<?php }?>
			</div>
			<div class="box ">
				<h4>系统环境</h4>
				<p><span class="wd">【GD】支持</span>
				   <?php echo extension_loaded('gd')&&function_exists('imagecreate')?'<span>支持GD<i class="dg bgImg"></i></span>':'<span>不支持GD(与图片有关的一些功能将不能使用)<i class="cw bgImg"></i></span>';?>
				</p>
				<p><span class="wd">【MySQL】支持</span>
					<?php if(extension_loaded('mysql')&&function_exists('mysql_connect')){
					echo '<span>支持Mysql<i class="dg bgImg"></i></span>';
					}else{
						echo '<span>不支持Mysql<i class="cw bgImg"></i></span>';
						$check=1;
					}?>
				</p>
				<p><span class="wd">【PHP版本】</span><?php echo PHP_VERSION;?></p>
				<p><span class="wd">【操作系统】</span><?php echo PHP_OS;?></p>
				<p><span class="wd">【服务器】</span><?php echo $_SERVER['SERVER_SOFTWARE'];?></p>
				<p><span class="wd">【服务器域名】</span><?php echo $_SERVER['HTTP_HOST'];?></p>
			</div>
		</div>
		<div class="act">
			<button type="button" class="prevStep">上一步：协议</button>
			<button type="button">重新检查</button>
			<button class="nextStep" <?php if($check){echo "style=\"display:none\" disabled=\"disabled\"";}?> type="button">下一步：配置系统</button>
		</div>
	</div>
</div>
<div class="miCopy">©2015-2017 zcutweb.com (智切网络技术旗下品牌)</div>
<script type="text/javascript">
$('.prevStep').click(function(){
	window.location.href='index.php?a=index';
});
$('.nextStep').click(function(){
	window.location.href='index.php?a=config';
});
</script>
</body>
</html>

