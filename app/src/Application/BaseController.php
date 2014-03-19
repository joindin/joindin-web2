<?php
namespace Application;

use Slim;

abstract class BaseController
{
    /** @var Slim */
    protected $application = null;

    protected $accessToken;
    protected $cfg;

    function __construct(Slim $app)
    {
        $this->application = $app;
        $this->defineRoutes($app);
		$this->cfg = $this->getConfig();

        $this->accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    }

    private function getConfig()
    {
        $app = Slim::getInstance();
        $config = $app->config('custom');
        return $config;
    }

    abstract protected function defineRoutes(Slim $app);
}
