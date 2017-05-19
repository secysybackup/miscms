<?php

return array (
    'URL_MODEL' => 2,
    'URL_HTML_SUFFIX' => '.html',
    'URL_PATHINFO_DEPR' => '/',
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' =>
        array (
            ':l/Tags/:model/:tag/:p' => 'Home/Tags/index',
            ':l/Tags/:tag/:p' => 'Home/Tags/index',
            ':l/Tags/:model/:tag' => 'Home/Tags/index',
            ':l/Tags/:p\d' => 'Home/Tags/index',
            ':l/Tags/:tag' => 'Home/Tags/index',
            ':l/Tags' => 'Home/Tags/index',
            'Tags/:model/:tag/:p' => 'Home/Tags/index',
            'Tags/:tag/:p' => 'Home/Tags/index',
            'Tags/:model/:tag' => 'Home/Tags/index',
            'Tags/:p\d' => 'Home/Tags/index',
            'Tags/:tag' => 'Home/Tags/index',
            'Tags' => 'Home/Tags/index',

            '/^user$/' => 'User/index/index',
            '/^user\/login$/' => 'User/Account/login',
            '/^user\/register$/' => 'User/Account/register',
            '/^user\/forgetpwd/' => 'User/Account/forgetpwd',

            /*内容文档路由*/
            '/^(en|cn)$/' => 'Index/index?l=:1',
            '/^(en|cn)\/([\w^_]+)\/-(\d+)-(\d+)-(\d+)$/' => 'Content/detail?l=:1&catdir=:2&catid=:3&id=:4&p=:5',
            '/^([\w^_]+)\/-(\d+)-(\d+)-(\d+)$/' => 'Content/detail?catdir=:1&catid=:2&id=:3&p=:4&',
            '/^(en|cn)\/([\w^_]+)\/(\d+)-(\d+)$/' => 'Content/detail?l=:1&catdir=:2&catid=:3&id=:4',
            '/^([\w^_]+)\/(\d+)-(\d+)$/' => 'Content/detail?catdir=:1&catid=:2&id=:3&',
            '/^(en|cn)\/([\w^_]+)-(\d+)-(\d+)$/' => 'Content/index?l=:1&catdir=:2&catid=:3&p=:4',
            '/^([\w^_]+)-(\d+)-(\d+)$/' => 'Content/index?catdir=:1&catid=:2&p=:3',
            '/^(en|cn)\/([\w^_]+)$/' => 'Content/index?l=:1&catdir=:2',
            '/^([\w^_]+)$/' => 'Content/index?catdir=:1',
        ),
);