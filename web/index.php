<?php

// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file

if (in_array(substr($_SERVER['REQUEST_URI'], -4), ['.css', '.jpg', '.png'])) {
	return false;
}

// include dependencies
require '../vendor/autoload.php';

session_cache_limiter(false);
session_start();

// include view controller
require '../app/src/View/Filters.php';
require '../app/src/View/Functions.php';

$config = array();
$configFile = realpath(__DIR__ . '/../config/config.php');
if (is_readable($configFile)) {
    include $configFile;
} else {
    include realpath(__DIR__ . '/../config/config.php.dist');
}

// Wrap the Config Data with the Application Config object
$config['slim']['custom'] = new \Application\Config($config['slim']['custom']);

// initialize Slim
$app = new \Slim\Slim(
    array_merge(
        $config['slim'],
        array(
            'view' => new \Slim\Views\Twig(),
        )
    )
);

$app->configureMode('development', function () use ($app) {
    error_reporting(-1);
    ini_set('display_errors', 1);
    ini_set('html_errors', 1);
    ini_set('display_startup_errors', 1);
});

// Pass the current mode to the template, so we can choose to show
// certain things only if the app is in live/development mode
$app->view()->appendData(
    array('slim_mode' => $config['slim']['mode'])
);

// Other variables needed by the main layout.html.twig template
$app->view()->appendData(
    array(
        'google_analytics_id' => $config['slim']['custom']['googleAnalyticsId'],
        'user' => (isset($_SESSION['user']) ? $_SESSION['user'] : false),
    )
);

// set Twig base folder, view folder and initialize Joindin filters
$app->view()->parserDirectory = realpath(__DIR__ . '/../vendor/Twig/lib/Twig');
$app->view()->setTemplatesDirectory('../app/templates');
View\Filters\initialize($app->view()->getEnvironment(), $app);
View\Functions\initialize($app->view()->getEnvironment(), $app);

if (isset($config['slim']['twig']['cache'])) {
    $app->view()->getEnvironment()->setCache($config['slim']['twig']['cache']);
} else {
    $app->view()->getEnvironment()->setCache(false);
}

$app->configureMode('development', function () use ($app) {
    $env = $app->view()->getEnvironment();
    $env->enableDebug();
    $env->addExtension(new \Twig_Extension_Debug());
});

// register error handlers
$app->error(function (\Exception $e) use ($app) {
    $app->render('Error/error.html.twig', ['exception' => $e]);
});

$app->notFound(function () use ($app) {
    $app->render('Error/404.html.twig');
});

// register middlewares
$app->add(new Middleware\ValidationMiddleware());
$app->add(new Middleware\FormMiddleware());

// register routes
new Application\ApplicationController($app);
new Event\EventController($app);
new Search\SearchController($app);
new User\UserController($app);
new Talk\TalkController($app);

// execute application
$app->run();
