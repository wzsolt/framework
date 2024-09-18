<?php
include_once 'cron.includes.php';

$framework = new Router(DEFAULT_HOST);
$framework->init();

$dayOfWeek  = (int) date('N');  // 1 (Monday) to 7 (Sunday)
$day        = (int) date('j');  // 1 to 31
$month      = (int) date('n');  // 1 to 12
$hour       = (int) date('G');  // 0 to 23
$minute     = (int) date('i');  // 0 to 59
$time       = date('H:i');      // 00:00 to 23:59

set_time_limit(0);

// Run in every min
include_once __DIR__ . '/video-processor.php';
