<?php

set_time_limit(0);
require_once 'vendor/autoload.php';
date_default_timezone_set('Asia/Kolkata');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ('test' == DEPLOY_ENV) {
	define('DB_HOST', '127.0.0.1');
	define('DB_USER', 'root');
	define('DB_PASS', 'root1234');
	define('DB_NAME', 'testdb_mvc');
}

function catchableErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (error_reporting() !== 0) {
		print_r("no = $errno str = $errstr file = $errfile line = $errline");
	}
}

set_error_handler('catchableErrorHandler');
