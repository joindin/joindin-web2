<?php

namespace Application;

use Joindin\Api\Client;
use Slim\Middleware as SlimMiddleware;

class ServiceProvider extends SlimMiddleware
{
    const SERVICE_API_CLIENT = 'api_client';
    const SERVICE_CACHE = 'cache';

    /** @var array */
    protected $customConfig = array();

    /**
     * Initializes all application-wide services.
     *
     * @return void
     */
    public function call()
    {
        $this->customConfig = $this->app->config('custom');

        $apiClient = function () {
            $accessToken = null;
            if (isset($_SESSION['access_token'])) {
                $accessToken = $_SESSION['access_token'];
            }

            return new Client(
                array('base_url' => $this->customConfig['apiUrl'], 'access_token' => $accessToken)
            );
        };

        $cacheService = function () {
            return new CacheService($this->customConfig['redis']['keyPrefix']);
        };

        $this->app->container->singleton(self::SERVICE_API_CLIENT, $apiClient);
        $this->app->container->singleton(self::SERVICE_CACHE, $cacheService);

        $this->next->call();
    }
}
