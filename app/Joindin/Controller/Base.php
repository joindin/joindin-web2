<?php
namespace Joindin\Controller;

use \Joindin\Service\Helper\Config as Config;

abstract class Base
{
    /** @var \Slim */
    protected $application = null;

    protected $accessToken;
    protected $cfg;

    function __construct(\Slim $app)
    {
        $this->application = $app;
        $this->defineRoutes($app);
		$cfg = new Config();
		$this->cfg = $cfg->getConfig();

        $this->accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    }

    abstract protected function defineRoutes(\Slim $app);
}
