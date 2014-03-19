<?php
namespace Application;

use Event\EventDb;
use Event\EventApi;

class ApplicationController extends BaseController
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

        $cache = new CacheService($keyPrefix);
        $event_collection = new EventApi($this->cfg, $this->accessToken, new EventDb($cache));
        $hot_events = $event_collection->getCollection($perPage, $start, 'hot');

        try {
            echo $this->application->render(
                'Application/index.html.twig',
                array(
                    'events' => $hot_events,
                    'page' => $page,
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
