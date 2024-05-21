<?php

//session_start();
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

use b24App\b24App;

require_once(dirname(__FILE__) . '/systemsApp/systems.php');
require_once(dirname(__FILE__) . '/systemsApp/define.php');
require_once(dirname(__FILE__) . '/systemsApp/router.php');

require_once(dirname(__FILE__) . '/systemsApp/SplClassLoader.php');
$loader = new SplClassLoader('b24App', 'b24App');
$loader->register();

$loader = new SplClassLoader('PHPMailer', 'b24App');
$loader->register();

$router = new router;

//phpinfo();
//die;

if (!$router->getRouteToClass($_SERVER['REQUEST_URI'])) {
    header('HTTP/1.0 404 Forbidden');
}