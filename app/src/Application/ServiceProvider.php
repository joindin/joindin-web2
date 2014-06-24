<?php

namespace Application;

use Joindin\Api\Client;
use Slim\Middleware as SlimMiddleware;

class ServiceProvider extends SlimMiddleware
{
    protected $customConfig = array();

    /**
     * Initializes all application-wide services.
     */
    public function call()
    {
        $this->customConfig = $this->app->config('custom');

        $apiClient = function () {
            $accessToken  = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
            $url          = $this->customConfig['apiUrl'];

            return new Client(array('base_url' => $url, 'access_token' => $accessToken));
        };

        $cacheService = function () {
            return new CacheService($this->customConfig['redis']['keyPrefix']);
        };

        $this->app->container->singleton('api_client', $apiClient);
        $this->app->container->singleton('cache', $cacheService);

        $this->next->call();
    }
}