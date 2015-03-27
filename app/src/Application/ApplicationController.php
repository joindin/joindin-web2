<?php
namespace Application;

use Event\EventDb;
use Event\EventApi;
use User\UserDb;
use User\UserApi;

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

        $eventApi = $this->getEventApi();
        $hotEvents = $eventApi->getEvents($perPage, $start, 'hot');
        $cfpEvents = $eventApi->getEvents(10, 0, 'cfp', true);

        $this->render(
            'Application/index.html.twig',
            array(
                'events' => $hotEvents,
                'cfp_events' => $cfpEvents,
                'page' => $page,
		'redirect' => $this->application->request()->get('redirect'),
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
        $this->render(
	    'Application/about.html.twig',
	    array(
		'redirect' => $this->getPath(),
	    )    
	);
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
        return new EventApi($this->cfg, $this->accessToken, $eventDb, $this->getUserApi());
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        $userDb = new UserDb($this->getCache());
        return new UserApi($this->cfg, $this->accessToken, $userDb);
    }
}
