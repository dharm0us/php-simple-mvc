<?php

spl_autoload_register(null);

// specify extensions that may be loaded 
spl_autoload_extensions('.php, .class.php');

function classLoader($class)
{
    $path = str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        include_once $path;
        return true;
    }
    return false;
}

spl_autoload_register('classLoader');
require_once 'vendor/autoload.php';
