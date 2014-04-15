<?php
namespace Event;

use Application\BaseController;
use Application\CacheService;
use Talk\TalkDb;
use Talk\TalkApi;

class EventController extends BaseController
{
    private $eventsToShow = 10;

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event', array($this, 'hotEvents'))->name("events-index");
        $app->get('/event/:friendly_name', array($this, 'details'))->name("event-detail");
        $app->get('/event/:friendly_name/map', array($this, 'map'))->name("event-map");
        $app->get('/event/:friendly_name/schedule', array($this, 'schedule'))->name("event-schedule");
        $app->post('/event/:friendly_name/add-comment', array($this, 'addComment'))->name('event-add-comment');
        $app->get('/e/:stub', array($this, 'quicklink'))->name("event-quicklink");
        $app->get('/event/attend/:friendly_name', array($this, 'attend'))->name("event-attend");
    }

    protected function getEventApi()
    {
        $keyPrefix = $this->cfg['redis']['keyPrefix'];
        $cache = new CacheService($keyPrefix);
        $eventDb = new EventDb($cache);
        $eventApi = new EventApi($this->cfg, $this->accessToken, $eventDb);
        return $eventApi;
    }

    public function hotEvents()
    {
        $eventApi = $this->getEventApi();
        $events = $eventApi->getCollection(
            $this->eventsToShow,
            null,
            'hot'
        );

        echo $this->render(
            'Event/index.html.twig',
            array(
                'events' => $events
            )
        );
    }

    public function details($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);
        if($event) {
            $quicklink = $this->application->request()->headers("host") 
                . $this->application->urlFor(
                    "event-quicklink", 
                    array("stub" => $event->getStub()
                ));

            $comments = $eventApi->getComments($event->getCommentsUri(), true);
            echo $this->render(
                'Event/details.html.twig',
                array(
                    'event' => $event,
                    'quicklink' => $quicklink,
                    'comments' => $comments,
                )
            );
        } else {
            $events_url = $this->application->urlFor("events");
            $this->application->redirect($events_url);
        }

    }


    public function map($friendly_name)
    {
        $eventApi = $this->getEventApi();

        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if($event) {
            echo $this->render(
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
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if($event) {
            $keyPrefix = $this->cfg['redis']['keyPrefix'];
            $cache = new CacheService($keyPrefix);
            $talkDb = new TalkDb($cache);
            $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb);
            $scheduler = new EventScheduler($talkApi);

            $schedule = $scheduler->getScheduleData($event);

            echo $this->render(
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
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByStub($stub);
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

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);
        if ($event) {
            $eventApi->addComment($event, $comment);
        }

        $url = $this->application->urlFor("event-detail", array('friendly_name' => $friendly_name));
        $this->application->redirect($url);
    }

    public function attend($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $eventApi->attend($event, $_SESSION['user']);
        }

        $url = '/';
        $r = $this->application->request()->get('r');
        if ($r) {
            $url = $this->application->urlFor("event-detail", array('friendly_name' => $r));
        }
        $this->application->redirect($url);
    }
}
