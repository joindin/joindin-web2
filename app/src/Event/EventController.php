<?php
namespace Event;

use Application\BaseController;
use Talk\TalkDb;
use Talk\TalkApi;

class EventController extends BaseController
{
    private $eventsToShow = 10;

    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/event', array($this, 'index'))->name("events-index");
        $app->get('/event/:friendly_name', array($this, 'details'))->name("event-detail");
        $app->get('/event/:friendly_name/map', array($this, 'map'))->name("event-map");
        $app->get('/event/:friendly_name/schedule', array($this, 'schedule'))->name("event-schedule");
        $app->post('/event/:friendly_name/add-comment', array($this, 'addComment'))->name('event-add-comment');
        $app->get('/e/:stub', array($this, 'quicklink'))->name("event-quicklink");
        $app->get('/event/xhr-attend/:friendly_name', array($this, 'xhrAttend'));
        $app->get('/event/attend/:friendly_name', array($this, 'attend'))->name("event-attend");
    }

    protected function getEventApi()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $eventDb = new EventDb($cache);
        $eventApi = new EventApi($this->cfg, $this->accessToken, $eventDb);
        return $eventApi;
    }

    public function index()
    {

        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $perPage = 10;
        $start = ($page -1) * $perPage;

        $eventApi = $this->getEventApi();
        $events = $eventApi->getCollection(
            $this->eventsToShow,
            $start,
            'upcoming'
        );

        $this->render(
            'Event/index.html.twig',
            array(
                'page' => $page,
                'events' => $events
            )
        );
    }

    public function details($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);
        if ($event) {
            $quicklink = $this->application->request()->headers("host")
                . $this->application->urlFor(
                    "event-quicklink",
                    array("stub" => $event->getStub())
                );

            $comments = $eventApi->getComments($event->getCommentsUri(), true);
            $this->render(
                'Event/details.html.twig',
                array(
                    'event' => $event,
                    'quicklink' => $quicklink,
                    'comments' => $comments,
                )
            );
        } else {
            $events_url = $this->application->urlFor("events-index");
            $this->application->redirect($events_url);
        }

    }


    public function map($friendly_name)
    {
        $eventApi = $this->getEventApi();

        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $this->render(
                'Event/map.html.twig',
                array(
                    'event' => $event
                )
            );
        } else {
            $events_url = $this->application->urlFor("events-index");
            $this->application->redirect($events_url);
        }
    }

    public function schedule($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $keyPrefix = $this->cfg['redisKeyPrefix'];
            $cache = new CacheService($keyPrefix);
            $talkDb = new TalkDb($cache);
            $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb);
            $scheduler = new EventScheduler($talkApi);

            $schedule = $scheduler->getScheduleData($event);

            $this->render(
                'Event/schedule.html.twig',
                array(
                    'event' => $event,
                    'eventDays' => $schedule,
                )
            );
        } else {
            $events_url = $this->application->urlFor("events-index");
            $this->application->redirect($events_url);
        }
    }

    public function quicklink($stub)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByStub($stub);
        if ($event) {
            $this->application->redirect(
                $this->application->urlFor("event-detail", array("friendly_name" => $event->getUrlFriendlyName())),
                301
            );
        } else {
            $events_url = $this->application->urlFor("events-index");
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
            $attendance = new EventAttendance($this->getEventApi());
            $attendance->confirm($event, $_SESSION['user']);
        }

        $url = '/';
        $r = $this->application->request()->get('r');
        $keyword = $this->application->request()->get('keyword');
        $page = $this->application->request()->get('page');

        if ($r) {
            $url = $this->application->urlFor('event-detail', array('friendly_name' => $r));
        }

        if ($keyword && is_numeric($page)) {
            $queryString = http_build_query(array('page' => $page, 'keyword' => $keyword));
            $url = $this->application->urlFor('search-events') . '?' . $queryString;
        }

        if (is_numeric($page) && !$keyword) {
            $url = $this->application->urlFor('events') . '?' . http_build_query(array('page' => $page));
        }

        $this->application->redirect($url);
    }

    public function xhrAttend($friendly_name)
    {
        $this->application->response()->header('Content-Type', 'application/json');

        $api = $this->getEventApi();
        $event = $api->getByFriendlyUrl($friendly_name);

        if ($event) {
            $attendance = new EventAttendance($this->getEventApi());
            $result = $attendance->confirm($event, $SESSION['user']);
        }

        $this->application->response()->body(json_encode(array('success' => $result)));
    }
}
