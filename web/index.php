<?php

// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file

if (in_array(substr($_SERVER['REQUEST_URI'], -4), ['.css', '.jpg', '.png'])) {
    return false;
}
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// include dependencies
require '../vendor/autoload.php';

session_set_cookie_params(60*60*24*7); // One week cookie
session_cache_limiter(false);
session_start();

$config = [];

$configFile = realpath(__DIR__ . '/../config/config.php');
if (is_readable($configFile)) {
    include $configFile;
} else {
    include realpath(__DIR__ . '/../config/config.php.dist');
}

// Wrap the Config Data with the Application Config object
$config['slim']['custom'] = new \JoindIn\Web\Application\Config($config['slim']['custom']);

// initialize Slim
$app = new \Slim\Slim(
    array_merge(
        $config['slim'],
        [
            'view' => new \Slim\Views\Twig(),
        ]
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
    ['slim_mode' => $config['slim']['mode']]
);

// Other variables needed by the main layout.html.twig template
$app->view()->appendData([
    'google_analytics_id' => $config['slim']['custom']['googleAnalyticsId'],
    'user'                => (isset($_SESSION['user']) ? $_SESSION['user'] : false),
]);

// set Twig base folder, view folder and initialize Joindin filters
$app->view()->parserDirectory = realpath(__DIR__ . '/../vendor/Twig/lib/Twig');
$app->view()->setTemplatesDirectory('../app/templates');

$app->view()->getEnvironment()->addExtension(new \JoindIn\Web\View\FiltersExtension());
$app->view()->getEnvironment()->addExtension(new \Slim\Views\TwigExtension());
$app->view()->getEnvironment()->addExtension(new \JoindIn\Web\View\FunctionsExtension($app));

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
    error_log(get_class($e) . ': ' . $e->getMessage() . " -- " . $e->getTraceAsString());
    $app->render('Error/error.html.twig', ['exception' => $e]);
});

$app->notFound(function () use ($app) {
    $app->render('Error/404.html.twig');
});

// register middlewares
$app->add(new \JoindIn\Web\Middleware\ValidationMiddleware());

$csrfSecret = null;
if (!empty($config['slim']['custom']['csrfSecret'])) {
    $csrfSecret = $config['slim']['custom']['csrfSecret'];
}
$app->add(new \JoindIn\Web\Middleware\FormMiddleware($csrfSecret));

// register services
$app->container->set('access_token', isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null);

$app->container->singleton(\JoindIn\Web\Application\CacheService::class, function ($container) {
    $redis = $container->settings['custom']['redis'];
    $prefix = $redis['options']['prefix'];

    if ($host = getenv('REDIS_HOST')) {
        $redis['connection'] = "tcp://$host:6379";
    }

    $client = new Predis\Client($redis['connection']);
    return new \JoindIn\Web\Application\CacheService($client, $prefix);
});
$app->container->singleton(\JoindIn\Web\Application\ContactApi::class, function ($container) {
    return new \JoindIn\Web\Application\ContactApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\JoindIn\Web\User\UserDb::class, function ($container) {
    return new \JoindIn\Web\User\UserDb($container[\JoindIn\Web\Application\CacheService::class]);
});
$app->container->singleton(\JoindIn\Web\User\UserApi::class, function ($container) {
    return new \JoindIn\Web\User\UserApi(
        $container['settings']['custom'],
        $container['access_token'],
        $container[\JoindIn\Web\User\UserDb::class]
    );
});
$app->container->singleton(\JoindIn\Web\Event\EventDb::class, function ($container) {
    return new \JoindIn\Web\Event\EventDb($container[\JoindIn\Web\Application\CacheService::class]);
});
$app->container->singleton(\JoindIn\Web\Event\EventApi::class, function ($container) {
    return new \JoindIn\Web\Event\EventApi(
        $container['settings']['custom'],
        $container['access_token'],
        $container[\JoindIn\Web\Event\EventDb::class],
        $container[\JoindIn\Web\User\UserApi::class]
    );
});
$app->container->singleton(\JoindIn\Web\Talk\TalkDb::class, function ($container) {
    return new \JoindIn\Web\Talk\TalkDb($container[\JoindIn\Web\Application\CacheService::class]);
});
$app->container->singleton(\JoindIn\Web\Talk\TalkApi::class, function ($container) {
    return new \JoindIn\Web\Talk\TalkApi(
        $container['settings']['custom'],
        $container['access_token'],
        new \JoindIn\Web\Talk\TalkDb($container[\JoindIn\Web\Application\CacheService::class]),
        $container[\JoindIn\Web\User\UserApi::class]
    );
});
$app->container->singleton(\JoindIn\Web\User\AuthApi::class, function ($container) {
    return new \JoindIn\Web\User\AuthApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\JoindIn\Web\Language\LanguageApi::class, function ($container) {
    return new \JoindIn\Web\Language\LanguageApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\JoindIn\Web\Talk\TalkTypeApi::class, function ($container) {
    return new \JoindIn\Web\Talk\TalkTypeApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\JoindIn\Web\Event\TrackApi::class, function ($container) {
    return new \JoindIn\Web\Event\TrackApi($container['settings']['custom'], $container['access_token']);
});
$app->container->singleton(\JoindIn\Web\Client\ClientApi::class, function ($container) {
    return new \JoindIn\Web\Client\ClientApi($container['settings']['custom'], $container['access_token']);
});

// register routes
new \JoindIn\Web\Application\ApplicationController($app);
new \JoindIn\Web\Event\EventController($app);
new \JoindIn\Web\Search\SearchController($app);
new \JoindIn\Web\User\UserController($app);
new \JoindIn\Web\Talk\TalkController($app);
new \JoindIn\Web\Client\ClientController($app);
new \JoindIn\Web\Apikey\ApikeyController($app);

// execute application
$app->run();
