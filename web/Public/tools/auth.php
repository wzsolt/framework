<?php

use Framework\Models\Database\Db;

$auth_realm = 'Restricted area';

if (empty($_SESSION['authenticated_supervisor']) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $res = Db::create()->getFirstRow(
        Db::select(
            'users',
            [
                'us_id AS userId',
                'us_code AS userCode',
                'us_firstname AS firstName',
                'us_lastname AS lastName',
                'us_email AS email',
                'us_password',
            ],
            [
                'us_email' => $_SERVER['PHP_AUTH_USER'],
                'us_enabled' => 1,
                'us_deleted' => 0,
                'us_superuser' => 1,
            ]
        )
    );

	if (!empty($res) && password_verify($_SERVER['PHP_AUTH_PW'], $res['us_password'])) {
		unset($res['us_password']);

		$_SESSION['authenticated_supervisor'] = $res;
	}
}

if (!empty($_REQUEST['logout'])) {

    $_SESSION['authenticated_supervisor'] = '';
	unset($_SESSION['authenticated_supervisor']);

    header('Location: /tools/');
    header('HTTP/1.0 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="' . $auth_realm . '"');
    exit();
}

if (empty($_SESSION['authenticated_supervisor'])) {
    header('HTTP/1.0 401 Unauthorized');
	header('WWW-Authenticate: Basic realm="' . $auth_realm . '"');
	die('You are not authorized to visit this page!');
}
