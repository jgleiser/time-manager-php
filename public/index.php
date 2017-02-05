<?php
session_start();
require_once '../vendor/autoload.php';
include_once '../src/config/database_data.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(array(
    "settings" => $config
));

spl_autoload_register(function ($classname) {
    require_once("../src/classes/" . $classname . ".php");
});

include_once '../src/container.php';
include_once '../src/routes.php';

$app->run();
