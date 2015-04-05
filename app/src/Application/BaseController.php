<?php
namespace Application;

use Slim\Slim;
use Twig_Error_Runtime;

abstract class BaseController
{
    /** @var Slim */
    protected $application = null;

    protected $accessToken;
    protected $cfg;

    public function __construct(Slim $app)
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

    protected function render($template, $data = array(), $status = null)
    {
        try {
            $this->application->render($template, $data, $status);
        } catch (Twig_Error_Runtime $e) {
            $this->application->render(
                'Error/app_load_error.html.twig',
                array(
                    'message' => sprintf(
                        'An exception has been thrown during the rendering of a template ("%s").',
                        $e->getMessage()
                    ),
                    -1,
                    null,
                    $e
                )
            );
        }
    }

    abstract protected function defineRoutes(Slim $app);
}
