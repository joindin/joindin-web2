<?php
namespace Joindin\Controller;

use \Joindin\Service\Helper\Config as Config;
use \Joindin\Service\Cache as Cache;
use Joindin\Model\Db\Event as DbEvent;

class Application extends Base
{
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/', array($this, 'index'));
        $app->get('/apps', array($this, 'apps'))->name('apps');
        $app->get('/about', array($this, 'about'))->name('about');
    }

    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 6;
        $start = ($page -1) * $perPage;

        $keyPrefix = $this->cfg['redis']['keyPrefix'];

        $cache = new Cache($keyPrefix);
        $event_collection = new \Joindin\Model\API\Event($this->cfg, $this->accessToken, new DbEvent($cache));
        $hot_events = $event_collection->getCollection($perPage, $start, 'hot');

        try {
            echo $this->application->render(
                'Application/index.html.twig',
                array(
                    'events' => $hot_events
                )
            );
        } catch (\Twig_Error_Runtime $e) {
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

    public function apps()
    {
        echo $this->application->render('Application/apps.html.twig');
    }

    /**
     * Render the about page
     */
    public function about()
    {
        echo $this->application->render(
            'Application/about.html.twig'
        );
    }
}
