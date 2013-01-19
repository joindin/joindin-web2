<?php
namespace Joindin;

spl_autoload_register('Joindin\autoload');

session_cache_limiter(false);
session_start();
ini_set('display_errors', 'on');

// include dependencies
require '../Vendor/Slim/Slim.php';
require '../Vendor/TwigView.php';

// include view controller
require('../View/Filters.php');

// initialize Slim
$app = new \Slim(array(
    'mode' => 'development',
    'view' => new \TwigView()
));

// set Twig base folder, view folder and initialize Joindin filters
\TwigView::$twigDirectory = realpath(__DIR__ . '/../Vendor/Twig/lib/Twig');
$app->view()->setTemplatesDirectory('../View');
\Joindin\View\Filter\initialize($app->view()->getEnvironment());

// register routes
new Controller\Application($app);
new Controller\Event($app);

// execute application
$app->run();

function autoload($class)
{
    if (in_array('Joindin', explode('\\', $class))) {
        $path = str_replace('\\', '/', $class);
        $path = substr($path, 8);
        require_once('../'.$path.'.php');
    }
}