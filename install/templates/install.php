<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>WisCms系统安装程序</title>
    <script src="../public/admin/js/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/zcut.css" />
    <script type="text/javascript">
	$(document).ready(function(){
		$('.setup_process').scrollTop( $('.setup_process')[0].scrollHeight );
	});
	</script>
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
                <span class="num bgImg s2_on fl"></span>
                <span class="num bgImg s3_on mrn fl"></span>
            </div>
        </div>
        <div class="test3 setup_process">
            <?php
			$status = true;
			echo "<p>安装中,请稍后...</p>";


			//1. 获取表单信息
			$db_host = empty($_POST["localhost"])?"":trim($_POST["localhost"]);
			$db_port = empty($_POST["db_port"])?"":trim($_POST["db_port"]);
			$db_name = empty($_POST["db_name"])?"":trim($_POST["db_name"]);
			$db_user = empty($_POST["db_user"])?"":trim($_POST["db_user"]);
			$db_pass = empty($_POST["db_password"])?"":trim($_POST["db_password"]);
			$db_pre  = empty($_POST["db_pre"])?"":trim($_POST["db_pre"]);//表前缀

			$admin     = empty($_POST["admin"])?"":trim($_POST["admin"]);
			$password  = empty($_POST["password"])?"":$_POST["password"];
			$password2 = empty($_POST["password2"])?"":$_POST["password2"];
			$mail      = empty($_POST["mail"])?"":trim($_POST["mail"]);
			$is_data   = empty($_POST["is_data"])?"0":trim($_POST["is_data"]);


			if($password!=$password2){
				die("<p style='color:red'>管理员密码两次不一致！</p><a href='javascript:window.history.back()'>[返回]</a>");
			}else{
			 	$password = hash ( 'sha1', $password.'c653a6e39a9fcdf234bb0cb01655040d' );
			}

			//2. （连接数据库）效验一下数据库的账号和密码
			$link = @mysql_connect($db_host,$db_user,$db_pass) or die("
			<p style='color:red'>数据库连接失败！ </p>
			<a href='javascript:window.history.back()'>[返回]</a>
			");
			echo "<p>数据库连接成功！....</p>";
			mysql_set_charset("utf8");

			function list_tables($database)
			{
				$rs = mysql_query("SHOW TABLES FROM $database");
				$tables = array();
				while ($row = mysql_fetch_row($rs)) {
					$tables[] = $row[0];
				}
				mysql_free_result($rs);
				return $tables;
			}

			//清空数据库
			$tables = list_tables($db_name);
			foreach($tables as $table){
				$drop_sql = "DROP TABLE `{$table}`";
				mysql_query("use {$db_name}",$link);
				mysql_query($drop_sql,$link);
			}

			//3. 读取数据库的sql文件(表结构)
			$sql_content = file_get_contents("db/structure.sql");
			echo "<p>读取数据局库配置文件！....</p>";


			//4. 解析配置文件。形成建表语句数组
			preg_match_all("/CREATE TABLE `(.*?)`(.*?);/is",$sql_content,$sqllist);

			//5. 创建数据库
			if(!mysql_query("use {$db_name}",$link)){
				if(mysql_query("create database {$db_name}",$link)){
					echo "<p>数据库{$db_name}创建成功！....</p>";
				} else {
					echo ("<p style='color:red'>数据库{$db_name}创建失败！ </p>");
				}
				mysql_query("use {$db_name}",$link);//选择数据库
			} else {
				echo "数据库{$db_name}存在";
			}

			//6. 遍历创建表格
			foreach($sqllist[1] as $k=>$table){
				$sql1 = "DROP TABLE IF EXISTS `{$db_pre}{$table}`";
				$sql2 = "create table `{$db_pre}{$table}`{$sqllist[2][$k]}";
				//echo $sql."<br/><br/>";
				if(mysql_query($sql1,$link)){
					if(mysql_query($sql2,$link)){
						echo "<p>创建表格{$db_pre}{$table}成功！.....</p>";
					} else {
						die("<p style='color:red'>数据表{$db_pre}{$table}创建失败！ </p>
						<a href='javascript:window.history.back()'>[返回]</a>");
						exit;
					}
				}
			}

			//7。添加后台管理员账户信息
			$reg_time = time();
			$sql = "insert into `{$db_pre}user` (id,role,username,password,email,reg_time,status) values(1,1,'{$admin}','{$password}','{$mail}','{$reg_time}',1)";
			$r = mysql_query($sql,$link);
			$sql = "insert into `{$db_pre}role_user` (role_id, user_id) values(1,1)";
			$r = mysql_query($sql,$link);
			if($r){
				echo "<p>添加后台管理员账户成功！.....</p>";
			}else{
				die("<p style='color:red'>添加后台管理员账户失败！ </p>
				<a href='javascript:window.history.back()'>[返回]</a>");
				exit;
			}

			//8. 生成配置文件dbconfig.inc.php
			$confile = WEB_ROOT.'/apps/Conf/db.php';

			$configText = file_get_contents($confile);
			$reg=array(
					"/'DB_HOST'=>'.+?',/i",
					"/'DB_USER'.+?',/i",
					"/'DB_PWD'.+?',/i",
					"/'DB_NAME'.+?',/is",
					"/'DB_PREFIX'.+?',/is",
				  );

			$rep=array(
				  "'DB_HOST'=>'{$db_host}',",
				  "'DB_USER'=>'{$db_user}',",
				  "'DB_PWD'=>'{$db_pass}',",
				  "'DB_NAME'=>'{$db_name}',",
				  "'DB_PREFIX'=>'{$db_pre}',",
				);


			file_put_contents($confile, preg_replace($reg, $rep, $configText));
			echo "<p>成功写入配置文件信息。。。</p>";

			//从备份中提取测试数据
			$file = 'db/sys_data.sql';
			$file2 = 'db/area.sql';
			$v = import_data($file,$db_pre,$link);
			$v = import_data($file2,$db_pre,$link);
			if($v){
				echo "<p>导入系统数据。。。</p>";
			}else{
				$status = false;
				echo "<p style='color:red'>导入系统数据失败:<br/>{$v} </p>";
			}

			//测试数据
			if($is_data){
				//从备份中提取测试数据
				$file = 'db/test_data.sql';

				$v = import_data($file,$db_pre,$link);

				if($v){
					echo "<p>导入测试数据。。。</p>";
				}else{
					$status = false;
					echo "<p style='color:red'>导入测试数据失败:<br/>{$v} </p>";
				}
			}

			//9.生成一个安装锁文件install.lock
			if($status){
				file_put_contents(WEB_ROOT."install/install.lock",date("Y-m-d H:m:s"));

				//10. 关闭数据库
				echo "<p>安装成功。。。</p>";
			}


			?>
        </div>
        <?php if(true){?>
		<div class="act">
			<button type="button" class="toHome">进入首页</button>
			<button type="button" class="toAdmin">进入管理后台</button>
			</div>
		<?php }	?>
    </div>
</div>
<div class="miCopy">©2015-2017 zcutweb.com (智切网络技术旗下品牌)</div>
<script type="text/javascript">
$('.toHome').click(function(){
	window.location.href='<?php echo WEB_URL; ?>';
});
$('.toAdmin').click(function(){
	window.location.href='<?php echo WEB_URL; ?>/admin';
});
</script>
</body>
</html>
