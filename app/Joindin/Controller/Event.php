<?php
namespace Joindin\Controller;


class Event extends Base
{

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event', array($this, 'index'));
        $app->get('/event/:friendly_name', array($this, 'details'));
        $app->get('/event/:friendly_name/map', array($this, 'map'));
        $app->get('/event/:friendly_name/schedule', array($this, 'schedule'));
        $app->get('/e/:stub', array($this, 'quicklink'));
    }

    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 10;
        $start = ($page -1) * $perPage;

        $event_collection = new \Joindin\Model\API\Event($this->accessToken);
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

    public function details($friendly_name)
    {
        $apiEvent = new \Joindin\Model\API\Event($this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);

        echo $this->application->render(
            'Event/details.html.twig',
            array(
                'event' => $event
            )
        );
    }


    public function map($friendly_name)
    {
        $apiEvent = new \Joindin\Model\API\Event($this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);

        echo $this->application->render(
            'Event/map.html.twig',
            array(
                'event' => $event
            )
        );
    }

     public function schedule($friendly_name)
     {
        $apiEvent = new \Joindin\Model\API\Event($this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);

        $apiTalk = new \Joindin\Model\API\Talk($this->accessToken);
        $scheduler = new \Joindin\Service\Scheduler($apiTalk);

        $schedule = $scheduler->getScheduleData($event);

        echo $this->application->render(
            'Event/schedule.html.twig',
            array(
                'event' => $event,
                'eventDays' => $schedule,
            )
        );
     }

    public function quicklink($stub)
    {
        $apiEvent = new \Joindin\Model\API\Event($this->accessToken);
        $event = $apiEvent->getByStub($stub);
        if($event) {
            $this->application->redirect($event->getUrl(), 301);
        }

        // we didn't find it
        return false;
    }
}
