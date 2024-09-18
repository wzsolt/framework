<?php

require_once __DIR__ . '/includes.php';
include_once DOC_ROOT . 'config/deploy-config.php';

$loader = new Framework\Psr4ClassAutoloader();
$loader->addNamespace('Framework', DIR_WEB . 'Lib');
$loader->addNamespace('Runners', DOC_ROOT . 'Deployment/Scripts');
$loader->register();

$payload = file_get_contents("php://input");

$deploy = new Framework\Deployment\Deploy(GIT_USER_NAME, DIR_GIT_REPO, $payload);

if ($deploy->verifySignature(GIT_SECRET) !== false) {

    $deploy->saveLog('Git webhook payload', $payload, date('Ymd') . '-git-webhook.txt');

    if($deploy->isOnBranch(GIT_BRANCH)) {

        if ($deploy->isDeploymentRequest()) {

            $deploy->start();

            $deploy->updateRepository();

            $deploy->updateDatabase();

            $deploy->runScripts();

            $deploy->finish()->sendReport(REPORT_EMAIL);
        }

    }else{
        echo "Invalid branch";
        exit();
    }

} else {
    // unverified
    http_response_code(403);
    echo "Unauthorized access";
    exit();
}
