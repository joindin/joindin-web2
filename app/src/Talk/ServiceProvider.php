<?php

namespace Talk;

use Slim\Middleware as SlimMiddleware;

class ServiceProvider extends SlimMiddleware
{
    protected $customConfig = array();
    protected $accessToken;

    public function call()
    {
        $this->customConfig = $this->app->config('custom');
        $this->accessToken  = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;

        $talkApi = function ($container) {
            return new TalkApi($this->customConfig, $this->accessToken, new TalkDb($container->cache));
        };

        $this->app->container->singleton('talk_api_service', $talkApi);

        $this->next->call();
    }
}
