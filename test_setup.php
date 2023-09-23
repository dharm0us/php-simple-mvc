<?php
$dir = dirname(__FILE__);
chdir($dir . '/../');
require_once 'env.php.test';
require_once 'common.inc.php';
require_once 'tests/TestUtils.php';
TestUtils::setUpTestDB();
