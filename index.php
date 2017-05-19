<?php

header("Content-type: text/html; charset=utf-8");
error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('UPLOAD_PATH', './uploads/');
define('APP_NAME', 'apps');
define('APP_PATH', './apps/');
define('APP_DEBUG', true);
define('TMPL_PATH', './themes/');
define('RUNTIME_PATH', './runtime/');
define('THINK_PATH','./core/');
define('ROOT', dirname(__FILE__));

require(THINK_PATH.'Core.php');