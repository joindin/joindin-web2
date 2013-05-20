<?php
namespace Joindin;

require_once '../src/Joindin/Service/Autoload.php';

spl_autoload_register('Joindin\Service\autoload::autoload');

session_cache_limiter(false);
session_start();
ini_set('display_errors', 'on');

// include dependencies
require '../vendor/Slim/Slim.php';
require '../vendor/TwigView.php';

// include view controller
require '../src/Joindin/View/Filters.php';

$config = array();
$configFile = realpath(__DIR__ . '/../config/config.php');
if (is_readable($configFile)) {
    include $configFile;
}

// initialize Slim
$app = new \Slim(
    array(
        'mode' => 'development',
        'view' => new \TwigView(),
        'custom' => $config,
    )
);

// set Twig base folder, view folder and initialize Joindin filters
\TwigView::$twigDirectory = realpath(__DIR__ . '/../Vendor/Twig/lib/Twig');
$app->view()->setTemplatesDirectory('../views');
\Joindin\View\Filter\initialize($app->view()->getEnvironment());

// register routes
new Controller\Application($app);
new Controller\Event($app);

// execute application
$app->run();
