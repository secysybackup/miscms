<?php

$config	= array(

    /* 模板相关配置 */
    'TMPL_CACHE_ON'       => false,
    'TMPL_CACHE_TIME'     => 3600,
    'TMPL_PARSE_STRING'   => array(
        '__PUBLIC__' => __ROOT__ . '/public',
        '__STATIC__' => __ROOT__ . '/public/static',
        '__IMG__'    => __ROOT__ . '/themes/User/default/Public/images',
        '__JS__'     => __ROOT__ . '/themes/User/default/Public/js',
        '__CSS__'    => __ROOT__ . '/themes/User/default/Public/css',
    ),

    // 是否开启子域名部署
    'APP_SUB_DOMAIN_DEPLOY' => false,

);
return $config;
?>
