<?php

return array(
    'DEFAULT_THEME'         => 'default',
    'DEFAULT_CHARSET'       =>  'utf-8',
    'APP_GROUP_LIST'        =>  'Home,Admin,Wap,api,User',
    'DEFAULT_GROUP'         =>  'Home',
    'DB_FIELDS_CACHE'       =>  false,
    'DB_FIELDTYPE_CHECK'    =>  true,
    'DEFAULT_MODULE'        =>  'Index',
    'LANG_SWITCH_ON'        =>  true,
    'ADMIN_ACCESS'          =>  'c653a6e39a9fcdf234bb0cb01655040d',
    'DEFAULT_LANG'          =>  'cn',
    'LANG_LIST'             =>  'cn,en',
    'LOAD_EXT_CONFIG'		=> 'url,db,version',					// 扩展配置

    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       =>  TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别

    /* Cookie设置 */
    'COOKIE_EXPIRE'         =>  '',    // Cookie有效期
    'COOKIE_PREFIX'         =>  'yzh_',


    /* 系统变量名称设置 */
    'VAR_PAGE'              =>  'p',

    // Think模板引擎标签库相关设定
    'TAGLIB_PRE_LOAD'       =>  'Gr',
    'TAGLIB_LOAD'           =>  true,

    /* URL设置 */
    'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_URLRULE' => '{$catdir}/{$catid}-{$id}.html|{$catdir}/-{$catid}-{$id}-{$page}.html:::{$catdir}.html|{$catdir}-{$catid}-{$page}.html',
    'TOKEN_ON' => '0',
    'TOKEN_NAME' => '__hash__',
    'TMPL_CACHE_ON' => '0',
    'TMPL_CACHE_TIME' => '-1',
    'HTML_CACHE_ON' => '0',
    'HTML_CACHE_TIME' => '60',
    'HTML_READ_TYPE' => '0',
    'HTML_FILE_SUFFIX' => '.html',
    'PAGE_LISTROWS' => '20',

    /* 模板引擎设置 */
    'TMPL_STRIP_SPACE'      =>  false,
    'TMPL_FILE_DEPR'        =>  '_',
    'TMPL_ACTION_ERROR'     =>  './public/error.html',
    'TMPL_ACTION_SUCCESS'   =>  './public/success.html',

    //分组域名功能
    'APP_SUB_DOMAIN_DEPLOY' => true, // 是否开启子域名部署
    'APP_SUB_DOMAIN_RULES'    => array(
        'm'    => array('Wap/'),  // m域名指向Wap分组
    ),
);
