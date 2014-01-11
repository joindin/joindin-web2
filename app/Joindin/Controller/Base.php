<?php
namespace Joindin\Controller;

abstract class Base
{
    /** @var \Slim */
    protected $application = null;

    protected $accessToken;

    function __construct(\Slim $app)
    {
        $this->application = $app;
        $this->defineRoutes($app);

        $this->accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    }

    abstract protected function defineRoutes(\Slim $app);
}
