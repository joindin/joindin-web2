<?php

namespace Event;

use Slim\Middleware as SlimMiddleware;

class ServiceProvider extends SlimMiddleware
{
    const SERVICE_API       = 'event_api_service';
    const SERVICE_SCHEDULER = 'event_scheduler';

    /**
     * Initializes all services associated with events.
     *
     * @return void
     */
    public function call()
    {
        $eventApi = function ($container) {
            return new EventApi($container->api_client, new EventDb($container->cache));
        };
        $eventScheduler = function ($container) {
            return new EventScheduler($container->talk_api_service);
        };

        $this->app->container->singleton(self::SERVICE_API, $eventApi);
        $this->app->container->singleton(self::SERVICE_SCHEDULER, $eventScheduler);

        $this->next->call();
    }
}