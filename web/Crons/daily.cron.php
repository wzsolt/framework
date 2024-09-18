<?php
include_once 'cron.includes.php';

$framework = new Router(DEFAULT_HOST);
$framework->init();

$dayOfWeek  = date('N');    // 1 (Monday) to 7 (Sunday)
$day        = date('j');    // 1 to 31
$month      = date('n');    // 1 to 12

