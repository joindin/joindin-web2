<?php
namespace Application;

use Slim\Slim;
use Twig\Error\RuntimeError;
use Twig_Error_Runtime;

abstract class BaseController
{
    protected \Slim\Slim $application;

    protected $accessToken;

    protected $cfg;

    public function __construct(Slim $slim)
    {
        $this->application = $slim;
        $this->defineRoutes($slim);
        $this->cfg = $this->getConfig();
        if (isset($_SESSION['access_token'])) {
            $this->accessToken = $_SESSION['access_token'];
        }
    }

    private function getConfig()
    {
        return Slim::getInstance()->config('custom');
    }

    protected function render($template, $data = [], $status = null): void
    {
        try {
            $this->application->render($template, $data, $status);
        } catch (RuntimeError $runtimeError) {
            $this->application->render(
                'Error/app_load_error.html.twig',
                [
                    'message' => sprintf(
                        'An exception has been thrown during the rendering of a template ("%s").',
                        $runtimeError->getMessage()
                    ),
                    -1,
                    null,
                    $runtimeError
                ]
            );
        }
    }

    /**
     * @param array-key $name
     * @param $default
     *
     * @return mixed|null
     */
    protected function getSessionVariable(string $name, $default = null)
    {
        return $_SESSION[$name] ?? $default;
    }

    abstract protected function defineRoutes(Slim $slim): void;
}
