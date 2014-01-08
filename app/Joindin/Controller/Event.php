<?php
namespace Joindin\Controller;

use \Joindin\Service\Helper\Config as Config;
use \Joindin\Model\API\Event as EventApi;
use \Joindin\Service\Db as DbService;


class Event extends Base
{
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event', array($this, 'index'))->name("events");
        $app->get('/event/:friendly_name', array($this, 'details'))->name("event-detail");
        $app->get('/event/:friendly_name/map', array($this, 'map'))->name("event-map");
        $app->get('/event/:friendly_name/schedule', array($this, 'schedule'))->name("event-schedule");
        $app->post('/event/:friendly_name/add-comment', array($this, 'addComment'))->name('event-add-comment');
        $app->get('/e/:stub', array($this, 'quicklink'))->name("event-quicklink");
    }

    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 10;
        $start = ($page -1) * $perPage;

        $event_collection = new EventApi(new Config(), $this->accessToken);
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
        $apiEvent = new EventApi(new Config(), $this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);

        if($event) {
            $quicklink = $this->application->request()->headers("host") 
                . $this->application->urlFor(
                    "event-quicklink", 
                    array("stub" => $event->getStub()
                ));

            echo $this->application->render(
                'Event/details.html.twig',
                array(
                    'event' => $event,
                    'quicklink' => $quicklink,

                )
            );
        } else {
            $events_url = $this->application->urlFor("events");
            $this->application->redirect($events_url);
        }

    }


    public function map($friendly_name)
    {
        $apiEvent = new EventApi(new Config(), $this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);

        if($event) {
            echo $this->application->render(
                'Event/map.html.twig',
                array(
                    'event' => $event
                )
            );
        } else {
            $events_url = $this->application->urlFor("events");
            $this->application->redirect($events_url);
        }
    }

     public function schedule($friendly_name)
     {
        $apiEvent = new EventApi(new Config(), $this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);

        if($event) {
            $apiTalk = new \Joindin\Model\API\Talk(new Config(), $this->accessToken);
            $scheduler = new \Joindin\Service\Scheduler($apiTalk);

            $schedule = $scheduler->getScheduleData($event);

            echo $this->application->render(
                'Event/schedule.html.twig',
                array(
                    'event' => $event,
                    'eventDays' => $schedule,
                )
            );
        } else {
            $events_url = $this->application->urlFor("events");
            $this->application->redirect($events_url);
        }

     }

    public function quicklink($stub)
    {
        $apiEvent = new EventApi(new Config(), $this->accessToken);
        $event = $apiEvent->getByStub($stub);
        if($event) {
            $this->application->redirect(
                $this->application->urlFor("event-detail", 
                    array("friendly_name" => $event->getUrlFriendlyName())),
                301
            );
        } else {
            $events_url = $this->application->urlFor("events");
            $this->application->redirect($events_url);
        }

    }

    public function addComment($friendly_name)
    {
        $request = $this->application->request();
        $comment = $request->post('comment');

        $apiEvent = new EventApi(new Config(), $this->accessToken);
        $event = $apiEvent->getByFriendlyUrl($friendly_name);
        if ($event) {
            $apiEvent->addComment($event, $comment);
        }

        $url = $this->application->urlFor("event-detail", array('friendly_name' => $friendly_name));
        $this->application->redirect($url);
    }
}
