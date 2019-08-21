<?php
namespace Application;

use Slim\Slim;
use Twig_Error_Runtime;

abstract class BaseController
{
    /** @var Slim */
    protected $application;

    protected $accessToken;
    protected $cfg;

    public function __construct(Slim $app)
    {
        $this->application = $app;
        $this->defineRoutes($app);
        $this->cfg = $this->getConfig();

        $this->accessToken = null;
        if (isset($_SESSION['access_token'])) {
            $this->accessToken = $_SESSION['access_token'];
        }
    }

    private function getConfig()
    {
        $app    = Slim::getInstance();
        $config = $app->config('custom');
        return $config;
    }

    protected function render($template, $data = [], $status = null)
    {
        try {
            $this->application->render($template, $data, $status);
        } catch (Twig_Error_Runtime $e) {
            $this->application->render(
                'Error/app_load_error.html.twig',
                [
                    'message' => sprintf(
                        'An exception has been thrown during the rendering of a template ("%s").',
                        $e->getMessage()
                    ),
                    -1,
                    null,
                    $e
                ]
            );
        }
    }

    protected function getSessionVariable($name, $default = null)
    {
        $value = $default;
        if (array_key_exists($name, $_SESSION)) {
            $value = $_SESSION[$name];
        }
        return $value;
    }

    abstract protected function defineRoutes(Slim $app);
}
