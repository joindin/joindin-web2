<?php
namespace Joindin\Controller;

class Application extends Base
{
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/', array($this, 'index'));
        $app->get('/oauth/callback', array($this, 'oauth_callback'));
    }

    public function index()
    {
        $event_collection = new \Joindin\Model\API\Event();
        $hot_events      = $event_collection->getCollection(5, 1, 'hot');
        $upcoming_events = $event_collection->getCollection(5, 1, 'upcoming');

        echo $this->application->render(
            'Application/index.html.twig',
            array(
                'hot_events'      => $hot_events,
                'upcoming_events' => $upcoming_events
            )
        );
    }

    public function oauth_callback()
    {
        $_SESSION['access_token'] = $this->application->request()->params('access_token');
        $this->application->redirect('/');
    }
}
