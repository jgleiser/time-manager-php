<?php
$container = $app->getContainer();

$container['view'] = new \Slim\Views\PhpRenderer("../templates/");

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('time-manager-log');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

?>