<?php
session_start();
require_once '../vendor/autoload.php';
include_once '../src/config/database_data.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(array(
    "settings" => $config
));

include_once '../src/container.php';
include_once '../src/routes.php';

$app->run();
