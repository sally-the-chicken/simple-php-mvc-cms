<?php 

function simple_php_mvc_cms_autoloader($class_name)
{
    $filename = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class_name)).'.php';
    $file = $filename;
    include_once $file;
}

spl_autoload_register('simple_php_mvc_cms_autoloader');

?>