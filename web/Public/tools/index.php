<?php

use Framework\Components\TwigExtension;
use Framework\Models\Database\Db;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;

require_once(__DIR__ . '/includes.php');

if(isset($_GET["getinfo"])){
    print phpinfo();
    exit;
}

$loader = new Framework\Psr4ClassAutoloader();
$loader->addNamespace('Framework', DIR_WEB . 'Lib');
$loader->register();

$framework = new Framework\Router('', $loader);
$framework->init();

$data = [];
$page = trim($_REQUEST['page'] ?? 'index');

require_once(__DIR__ . '/auth.php');

if(file_exists(__DIR__ . '/includes/model/' . $page . '.php')) {
    include_once('includes/model/' . $page . '.php');
}

$loader = new FilesystemLoader(
    __DIR__ . '/includes/view',
);

$twig = new Twig\Environment($loader, [
    'cache' => false,
    'debug' => true
]);

$twig->addExtension(new StringLoaderExtension());
$twig->addExtension(new TwigExtension());
$twig->addExtension(new DebugExtension());

$common = [
    'superuser' => $_SESSION['authenticated_supervisor'],
    'page' => $page,
    'pageTitle' => (MENU[$page]['name'] ?? ''),
];

$params = array_merge($common, $data);
echo $twig->render('header.twig', $params);

if(file_exists(__DIR__ . '/includes/view/' . $page . '.twig')) {
    echo $twig->render($page . TWIG_FILE_EXTENSION, $params);
}

echo $twig->render('footer.twig', array_merge($common, $params));

