<?php

namespace Event;

use Application\CacheService;
use Slim\Middleware as SlimMiddleware;
use Talk\TalkApi;
use Talk\TalkDb;

class Middleware extends SlimMiddleware
{
    protected $customConfig = array();
    protected $accessToken;

    public function call()
    {
        $this->customConfig = $this->app->config('custom');
        $this->accessToken  = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;

        $cacheService = function () {
            return new CacheService($this->customConfig['redis']['keyPrefix']);
        };

        $eventApi = function ($container) {
            return new EventApi($this->customConfig, $this->accessToken, new EventDb($container->cache));
        };

        $eventScheduler = function ($container) {
            return new EventScheduler($container->talk_api_service);
        };

        $talkApi = function ($container) {
            return new TalkApi($this->customConfig, $this->accessToken, new TalkDb($container->cache));
        };

        $this->app->container->singleton('cache', $cacheService);
        $this->app->container->singleton('event_api_service', $eventApi);
        $this->app->container->singleton('event_scheduler', $eventScheduler);
        $this->app->container->singleton('talk_api_service', $talkApi);

        $this->next->call();
    }
}