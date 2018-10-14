<?php

// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file

if (in_array(substr($_SERVER['REQUEST_URI'], -4), ['.css', '.jpg', '.png'])) {
	return false;
}

// include dependencies
require '../vendor/autoload.php';

session_set_cookie_params(60*60*24*7); // One week cookie
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

$csrfSecret = null;
if (!empty($config['slim']['custom']['csrfSecret'])) {
    $csrfSecret = $config['slim']['custom']['csrfSecret'];
}
$app->add(new Middleware\FormMiddleware($csrfSecret));

// register services
$app->container->set('access_token', isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null);

$app->container->singleton(\Application\CacheService::class, function ($container) {
    $client = new Predis\Client();
    $keyPrefix = $container->settings['custom']['redisKeyPrefix'];
    return new \Application\CacheService($client, $keyPrefix);
});
$app->container->singleton(\Application\ContactApi::class, function ($container) {
    return new \Application\ContactApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\User\UserDb::class, function ($container) {
    return new \User\UserDb($container[\Application\CacheService::class]);
});
$app->container->singleton(\User\UserApi::class, function ($container) {
    return new \User\UserApi(
        $container['settings']['custom'],
        $container['access_token'],
        $container[\User\UserDb::class]
    );
});
$app->container->singleton(\Event\EventDb::class, function ($container) {
    return new \Event\EventDb($container[\Application\CacheService::class]);
});
$app->container->singleton(\Event\EventApi::class, function ($container) {
    return new \Event\EventApi(
        $container['settings']['custom'],
        $container['access_token'],
        $container[\Event\EventDb::class],
        $container[\User\UserApi::class]
    );
});
$app->container->singleton(\Talk\TalkDb::class, function ($container) {
    return new \Talk\TalkDb($container[\Application\CacheService::class]);
});
$app->container->singleton(\Talk\TalkApi::class, function ($container) {
    return new \Talk\TalkApi(
        $container['settings']['custom'],
        $container['access_token'],
        new \Talk\TalkDb($container[\Application\CacheService::class]),
        $container[\User\UserApi::class]);
});
$app->container->singleton(\User\AuthApi::class, function ($container) {
    return new \User\AuthApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\Language\LanguageApi::class, function ($container) {
    return new \Language\LanguageApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\Talk\TalkTypeApi::class, function ($container) {
    return new \Talk\TalkTypeApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\Event\TrackApi::class, function ($container) {
    return new \Event\TrackApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\Client\ClientApi::class, function ($container) {
    return new \Client\ClientApi($container['settings']['custom'], $container['access_token']);
});

// register routes
new Application\ApplicationController($app);
new Event\EventController($app);
new Search\SearchController($app);
new User\UserController($app);
new Talk\TalkController($app);
new Client\ClientController($app);
new Apikey\ApikeyController($app);

// execute application
$app->run();
