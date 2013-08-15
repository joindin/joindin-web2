<?php
namespace Joindin\Controller;

class Application extends Base
{
    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 6;
        $start = ($page -1) * $perPage;

        $event_collection = new \Joindin\Model\API\Event();
        $hot_events = $event_collection->getCollection($perPage, $start, 'hot');
//        $upcoming_events = $event_collection->getCollection(12, 1, 'upcoming');
        try {
            echo $this->application->render(
                'Application/index.html.twig',
                array(
                    'hot_events' => $hot_events,
//                    'upcoming_events' => $upcoming_events,
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

    public function oauth_callback()
    {
        $_SESSION['access_token'] = $this->application->request()->params('access_token');
        $this->application->redirect('/');
    }

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/', array($this, 'index'));
        $app->get('/oauth/callback', array($this, 'oauth_callback'));
    }
}



