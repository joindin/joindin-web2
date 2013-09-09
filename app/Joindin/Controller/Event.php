<?php
namespace Joindin\Controller;


class Event extends Base
{

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event', array($this, 'index'));
        $app->get('/event/view/:id', array($this, 'show'));

    }

    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 10;
        $start = ($page -1) * $perPage;

        $event_collection = new \Joindin\Model\API\Event();
        $events = $event_collection->getCollection($perPage, $start);
        try {
            echo $this->application->render(
                'Event/index.html.twig',
                array(
                    'events' => $events,
                    'page' => $page,
                )
            );
        } catch (\Twig_Error_Runtime $e) {
            $this->application->render(
                'Error/app_load_error.html.twig',
                array(
                    'message' => sprintf(
                        'An exception has been thrown during the rendering of '.
                        'a template ("%s").',
                        $e->getMessage()
                    ),
                    -1,
                    null,
                    $e
                )
            );
        }
    }

    public function show($id)
    {
        $apiEvent = new \Joindin\Model\API\Event();
        $event = $apiEvent->getBySlug($id);

        echo $this->application->render(
            'Event/show.html.twig',
            array(
                'event' => $event->getTemplateData(),
            )
        );
    }


}
