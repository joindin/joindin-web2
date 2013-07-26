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

        try {
            echo $this->application->render(
                'Application/index.html.twig',
                array(
                    'hot_events'      => $hot_events,
                    'upcoming_events' => $upcoming_events
                )
            );
        } catch (\Twig_Error_Runtime $e) {
            $this->application->render('Error/app_load_error.html.twig',
                array(
                    'env' => $this->application->getMode(),
                    'message' => sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, null, $e)
            );
        }
    }

    public function oauth_callback()
    {
        $_SESSION['access_token'] = $this->application->request()->params('access_token');
        $this->application->redirect('/');
    }
}



