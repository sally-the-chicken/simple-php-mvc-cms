<?php

require_once 'config/environment.php';
require_once 'config/constants.php';
require_once 'bootstrap.php';

if (defined(ENVIRONMENT) && ENVIRONMENT === 'prod') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_start();
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 18000)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 18000) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// routing
if (isset($_REQUEST['q'])) {
    $q_arr = explode('/', $_REQUEST['q']);
    $q_arr = array_filter($q_arr, 'strlen');
    if (isset($q_arr[1])) {
        $_REQUEST['controller'] = trim(strtolower($q_arr[1]));
    }
    if (isset($q_arr[2])) {
        $_REQUEST['action'] = trim(strtolower($q_arr[2]));
    }
    if (count($q_arr) > 2) {
        // each pair becomes get param/value
        $i = 3;
        $gets = array();
        while (isset($q_arr[$i])) {
            if (isset($q_arr[$i + 1])) {
                $gets[$q_arr[$i]] = $q_arr[$i + 1];
            } else {
                $gets[$q_arr[$i]] = null;
            }
            $i = $i + 2;
        }
        $_REQUEST = array_merge($_REQUEST, $gets);
        $_GET = array_merge($_GET, $gets);
    }
}
$controller_name = isset($_REQUEST['controller']) ? $_REQUEST['controller'] : 'home';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
if ($action !== 'activate' && 
    !in_array($controller_name, array('login', 'signup')) &&
    (!isset($_SESSION[SESS_USER_VAR]) || !$_SESSION[SESS_USER_VAR]->is_authenticated())) {
    session_unset();
    $location = APP_DIR . "login/";
    header("location: " . $location);
    exit;
}

$controller_classname = 'Controller_' . ucfirst($controller_name);
if (class_exists($controller_classname)) {
    $controller = new $controller_classname();
} else {
    $controller = new Controller_Home();
}
$method_name = 'action_index';
if (method_exists($controller, 'action_' . $action)) {
    $method_name = 'action_' . $action;
} else {
    $controller = new Controller_Home();
    $method_name = 'action_index';
}
$view = new View();
if (substr($method_name, 0, 12) === "action_ajax_") {
    $view->ajax();
}
// end routing

$view->set_var('controller', $controller_classname);
$view->set_var('action', $action);
$view->set_content($controller_name . DIRECTORY_SEPARATOR . $action);

try {
    $controller->setView($view);
    $controller->init();
    $controller->$method_name();
} catch (Exception $e) {
    echo 'We have a situation here.';
    echo $e->getMessage();
    exit;
}
$view->show();
