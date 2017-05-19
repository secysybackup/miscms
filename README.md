##简介
Yuzihao（誉字号）是基于ThinkPHP3.1.3框架开发的多语言企业网站管理系统，提供更方便、更安全的WEB应用开发体验。

##环境要求
支持系统：Win/Linux
PHP版本：>=5.4
MySQL：>=5.x

##安装
安装请执行http://yourcode/install/index.php
安装完成后请删除或改名install/index.php

## 目录结构
```
├─index.php 入口文件
├─.htaccess 伪静态文件
├─robots.txt 
├─admin 后台入口
├─apps 应用模块目录
├─install 应用程序安装目录
├─core 框架目录
├─public 应用资源文件目录
│  ├─admin 后台模版样式
│  ├─browser 浏览器升级提示模版文件
│  ├─data 数据库备份目录
│  ├─images images目录
│  ├─static 第三方插件类库目录
│  ├─404.html 404页面
│  ├─error.html 错误消息跳转模版
│  ├─statistics.html 统计代码文件
│  └─success.html 成功消息跳转模版
│
├─rewrite 不同环境下的伪静态文件
│  ├─.htaccess  Linux环境下 Apache服务器
│  ├─httpd.ini  Windows环境下 iis6.0服务器
│  └─Web.Config  Windows环境下 iis7.5服务器
├─runtime 应用运行时目录
├─themes 模版文件目录
│  ├─Admin 后台模版
│  ├─Home pc端模版
│  └─Wap 手机端模版
│
└─uploads 上传根目录
```