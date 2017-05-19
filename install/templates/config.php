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
                <span class="num bgImg s2_on fl"></span>
                <span class="num bgImg s3_off mrn fl"></span>
            </div>
        </div>
        <div class="test1">
            <form name="form1" action="index.php?a=install" method="post" class="form-horizontal">
            <table>
                <tr>
                    <th>数据库信息</th>
                </tr>
                <tr>
                    <td class="tda">服务器地址：</td>
                    <td><input type="text" name='db_address' value='127.0.0.1' /></td>
                    <td>数据库服务器地址，一般为127.0.0.1</td>
                </tr>
                <tr>
                    <td class="tda">数据库端口：</td>
                    <td><input type="text" name='db_port' value='3306' /></td>
                    <td>系统数据库端口，一般为3306</td>
                </tr>
                <tr>
                    <td class="tda">数据库名称：</td>
                    <td><input type="text" name='db_name' autofocus="autofocus" /></td>
                    <td>系统数据库名,必须包含字母</td>
                </tr>
                <tr>
                    <td class="tda">用户名：</td>
                    <td><input type="text" name='db_user' /></td>
                    <td>连接数据库的用户名</td>
                </tr>
                <tr>
                    <td class="tda">密码：</td>
                    <td><input type="password" name='db_password' /></td>
                    <td>连接数据库的密码</td>
                </tr>
                <tr>
                    <td class="tda">数据库前缀：</td>
                    <td><input type="text"  name='db_pre'  value='wis_' /></td>
                    <td>建议使用默认,数据库前缀必须带 '_'</td>
                </tr>
            </table>
            <div class="xh"></div>
            <table>
                <tr>
                    <th>管理员账号</th>
                </tr>
                <tr>
                    <td class="tda">用户名：</td>
                    <td><input type="text" name="admin" value="zcut" /></td>
                    <td>管理员账号最少4位</td>
                </tr>
                <tr>
                    <td class="tda">密码：</td>
                    <td><input type="password" name="password" value="" /></td>
                    <td>保证密码最少6位</td>
                </tr>
                <tr>
                    <td class="tda">确认密码：</td>
                    <td><input type="password" name="password2" value=""/></td>
                </tr>
                <tr>
                    <td class="tda">管理邮箱：</td>
                    <td><input type="text"  name="mail" value=""/>

                </tr>
                <tr>
                    <td class="tda">演示数据：</td>
                    <td class="is"><input name="is_data" value="1" type="checkbox"/></td>
                </tr>
            </table>
        </div>
        <div class="act">
            <button type="button" class="prevStep">上一步：检测安装环境</button>
            <button type="submit">开始安装</button>
        </div>
    </div>
</div>
<div class="miCopy">©2015-2017 zcutweb.com (智切网络技术旗下品牌)</div>
<script type="text/javascript">
$('.prevStep').click(function(){
  window.location.href='index.php?a=check';
});
</script>
</body>
</html>
