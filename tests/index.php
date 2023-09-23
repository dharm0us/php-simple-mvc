<?php
$dir = dirname(__FILE__);
chdir($dir . '/../');
require_once 'env.php.test';
require_once 'common.inc.php';
require_once 'GenericWebViewController.php';
require_once 'PlayerController.php';
require_once 'CategoryController.php';

if (isset($_REQUEST['module'])) {
    $module = $_REQUEST['module'];
    if ($module == 'player') {
        $ctrl = new PlayerController();
        $ctrl->run();
    } else if ($module == 'category') {
        $ctrl = new CategoryController();
        $ctrl->run();
    }
} else {
    $ctrl = new GenericWebViewController();
    $ctrl->run();
}
