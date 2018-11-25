<?php 

require_once 'config/cli.php';
require_once 'bootstrap.php';

if (count($argv) < 2){
    $err = "Controller and method are required.\n#/usr/bin/php ".WEBROOT."cli.php [controller] [method] [optional]";
    die($err);
}

$controller_name    = $argv[1];
$action             = $argv[2];

$controller_classname = 'Controller_'.ucfirst($controller_name);
if (class_exists($controller_classname)){
    $controller = new $controller_classname();
} else {
    $controller = new Controller_Cli();
}
$method_name = 'action_default';
if (method_exists($controller, 'action_'.$action)){
    $method_name = 'action_'.$action;
} else {
    $controller  = new Controller_Cli();
    $method_name = 'action_default';
}


// TODO:  do some sort of logging / locking here

try{
    $arguments = array_slice($argv, 3);
    if (!empty($arguments)){
        $controller->$method_name($arguments);
    } else {
        $controller->$method_name();
    }
} catch (Exception $e){
    echo 'We have a situation here.';
    print_r($e);
    exit;
}

?>