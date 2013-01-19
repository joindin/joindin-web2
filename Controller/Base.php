<?php
namespace Joindin\Controller;

abstract class Base
{
    /** @var \Slim */
    protected $application = null;

    function __construct(\Slim $app)
    {
        $this->application = $app;
        $this->defineRoutes($app);
    }

    abstract protected function defineRoutes(\Slim $app);
}
