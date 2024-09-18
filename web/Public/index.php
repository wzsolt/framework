<?php
require_once __DIR__ . '/includes.php';

if(DEBUG_ON) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}

$loader = new Framework\Psr4ClassAutoloader();
$loader->addNamespace('Framework', DIR_WEB . 'Lib');
$loader->register();

if(Framework\Router::isApiRequest()){
    include_once __DIR__ . '/../../config/api-constants.php';

    $api = new Framework\Api\Api();
    $api->start();
}else{
    $framework = new Framework\Router('', $loader);

    $framework->init();

    try {
        $framework->display();
    }catch (Exception $e){
        die($e->getMessage());
    }
}