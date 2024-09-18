<?php
const WEB_ROOT = __DIR__ . '/';
const DOC_ROOT = __DIR__ . '/../../';

include_once DOC_ROOT . 'config/config.php';
include_once DOC_ROOT . 'config/constants.php';
include_once DOC_ROOT . 'web/Lib/Helpers/Helper.lib.php';
include_once DOC_ROOT . 'web/Lib/Psr4ClassAutoloader.php';

// Composer auto loader
require_once DOC_ROOT . 'vendor/autoload.php';
