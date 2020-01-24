<?php

namespace Middleware;

use Slim\Middleware;

/**
 * Redirect trailing slashes
 */
class TrailingSlashMiddleware extends Middleware
{
    /**
     * @var bool
     */
    private $addSlash;

    /**
     * Configure whether add or remove the slash.
     *
     * @param bool $addSlash
     */
    public function __construct($addSlash = false)
    {
        $this->addSlash = $addSlash;
    }

    /**
     * @return void
     */
    public function call()
    {
        $request = $this->app->request();
        $path    = $request->getPath();

        $hasSlash = strlen($path) > 1 && '/' === substr($path, -1);

        if ($this->addSlash && !$hasSlash) {
            $this->app->redirect($path.'/', 301);
        }

        if (!$this->addSlash && $hasSlash) {
            $this->app->redirect(substr($path, 0, -1), 301);
        }

        $this->next->call();
    }
}
