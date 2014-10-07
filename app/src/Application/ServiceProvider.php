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

            $client = new Client(array('base_url' => $this->customConfig['apiUrl'], 'access_token' => $accessToken));

            // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
            $client->setDefaultOption(
                'headers/Forwarded',
                sprintf(
                    'for=%s;user-agent="%s"',
                    $_SERVER['REMOTE_ADDR'],
                    isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
                )
            );

            return $client;
        };

        $cacheService = function () {
            return new CacheService($this->customConfig['redisKeyPrefix']);
        };

        $this->app->container->singleton(self::SERVICE_API_CLIENT, $apiClient);
        $this->app->container->singleton(self::SERVICE_CACHE, $cacheService);

        $this->next->call();
    }
}
