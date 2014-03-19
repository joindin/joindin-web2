<?php
namespace Application;

abstract class BaseController
{
    /** @var \Slim */
    protected $application = null;

    protected $accessToken;
    protected $cfg;

    function __construct(\Slim $app)
    {
        $this->application = $app;
        $this->defineRoutes($app);
		$cfg = new ConfigHelper();
		$this->cfg = $cfg->getConfig();

        $this->accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    }

    abstract protected function defineRoutes(\Slim $app);
}
