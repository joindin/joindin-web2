<?php
namespace Application;

use Event\EventDb;
use Event\EventApi;

class ApplicationController extends BaseController
{
    protected function defineRoutes(\Slim\Slim $app)
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

        $event_collection = $this->getEventApi();
        $hot_events = $event_collection->getFilteredCollection($perPage, $start, 'hot');
        $cfp_events = $event_collection->getFilteredCollection(10, 0, 'cfp', true);

        $this->render(
            'Application/index.html.twig',
            array(
                'events' => $hot_events,
                'cfp_events' => $cfp_events,
                'page' => $page,
            )
        );
    }

    public function apps()
    {
        $this->render('Application/apps.html.twig');
    }

    /**
     * Render the about page
     */
    public function about()
    {
        $this->render('Application/about.html.twig');
    }

    /**
     * @return CacheService
     */
    private function getCache()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        return new CacheService($keyPrefix);
    }

    /**
     * @return EventApi
     */
    private function getEventApi()
    {
        $eventDb = new EventDb($this->getCache());
        return new EventApi($this->cfg, $this->accessToken, $eventDb);
    }
}
