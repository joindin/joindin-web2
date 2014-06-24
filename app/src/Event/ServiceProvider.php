<?php

namespace Event;

use Joindin\Api\Description\Event\Comments;
use Joindin\Api\Description\Events;
use Slim\Middleware as SlimMiddleware;

class ServiceProvider extends SlimMiddleware
{
    const SERVICE_API       = 'event_api_service';
    const SERVICE_SCHEDULER = 'event_scheduler';
    const SERVICE_API_EVENT = 'api_event_service';
    const SERVICE_API_EVENT_COMMENT = 'api_event_comment_service';

    /**
     * Initializes all services associated with events.
     *
     * @return void
     */
    public function call()
    {
        $eventScheduler = function ($container) {
            return new EventScheduler($container->talk_api_service);
        };
        $apiEventService = function ($container) {
            return $container->api_client->getService(new Events());
        };
        $apiEventCommentService = function ($container) {
            return $container->api_client->getService(new Comments());
        };
        $eventApi = function ($container) {
            return new EventApi(
                new EventDb($container->cache),
                $container->api_event_service,
                $container->api_event_comment_service
            );
        };

        $this->app->container->singleton(self::SERVICE_SCHEDULER, $eventScheduler);
        $this->app->container->singleton(self::SERVICE_API_EVENT, $apiEventService);
        $this->app->container->singleton(self::SERVICE_API_EVENT_COMMENT, $apiEventCommentService);
        $this->app->container->singleton(self::SERVICE_API, $eventApi);

        $this->next->call();
    }
}