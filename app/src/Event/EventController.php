<?php
namespace Event;

use Application\BaseController;
use Application\CacheService;
use Slim\Exception\Stop;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator;
use Talk\TalkDb;
use Talk\TalkApi;
use User\UserDb;
use User\UserApi;
use Exception;
use Slim\Slim;

class EventController extends BaseController
{
    private $itemsPerPage = 10;

    protected function defineRoutes(\Slim\Slim $app)
    {
        // named routes first; should an event pick the same name then at least our actions take precedence
        $app->get('/event', array($this, 'index'))->name("events-index");
        $app->map('/event/submit', array($this, 'submit'))->via('GET', 'POST')->name('event-submit');
        $app->get('/event/callforpapers', array($this, 'callForPapers'))->name('event-call-for-papers');
        $app->get('/event/:friendly_name', array($this, 'details'))->name("event-detail");
        $app->get('/event/:friendly_name/map', array($this, 'map'))->name("event-map");
        $app->get('/event/:friendly_name/schedule', array($this, 'schedule'))->name("event-schedule");
        $app->get('/event/:friendly_name/talk-comments', array($this, 'talkComments'))->name("event-talk-comments");
        $app->post('/event/:friendly_name/add-comment', array($this, 'addComment'))->name('event-add-comment');
        $app->map('/event/:friendly_name/edit', array($this, 'edit'))->via('GET', 'POST')->name('event-edit');
        $app->get('/e/:stub', array($this, 'quicklink'))->name("event-quicklink");
        $app->get('/event/xhr-attend/:friendly_name', array($this, 'xhrAttend'));
        $app->get('/event/xhr-unattend/:friendly_name', array($this, 'xhrUnattend'));
        $app->get('/event/attend/:friendly_name', array($this, 'attend'))->name("event-attend");
        $app->get('/event/unattend/:friendly_name', array($this, 'unattend'))->name("event-unattend");
    }

    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $start = ($page -1) * $this->itemsPerPage;

        $eventApi = $this->getEventApi();
        $events = $eventApi->getEvents(
            $this->itemsPerPage,
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

    public function callForPapers()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $start = ($page -1) * $this->itemsPerPage;

        $eventApi = $this->getEventApi();
        $events = $eventApi->getEvents(
            $this->itemsPerPage,
            $start,
            'cfp',
            true
        );

        $this->render(
            'Event/call-for-papers.html.twig',
            array(
                'page' => $page,
                'events' => $events,
            )
        );
    }

    public function details($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            return Slim::getInstance()->notFound();
        }

        $quicklink = $this->application->request()->headers("host")
            . $this->application->urlFor('event-quicklink', array('stub' => $event->getStub()));

        $comments = $eventApi->getComments($event->getCommentsUri(), true);
        $this->render(
            'Event/details.html.twig',
            array(
                'event' => $event,
                'quicklink' => $quicklink,
                'comments' => $comments,
            )
        );
    }

    public function map($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            $this->redirectToListPage();
        }

        $this->render('Event/map.html.twig', array(
            'event' => $event,
        ));
    }

    public function talkComments($friendly_name)
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $start = ($page -1) * $this->itemsPerPage;

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $comments = $eventApi->getTalkComments(
                $event->getAllTalkCommentsUri(),
                $this->itemsPerPage,
                $start,
                true
            );

            // If we have comments, fetch talk slugs for the talks so that we can create links to them in the template
            $slugs = array();
            if (array_key_exists('comments', $comments) && $comments['pagination']->count > 0) {
                $slugs = $this->getTalkSlugsForTalkComments($comments['comments'], $event);
            }

            $this->render(
                'Event/talk-comments.html.twig',
                array(
                    'event' => $event,
                    'page' => $page,
                    'talkComments' => $comments,
                    'talkSlugs' => $slugs,
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

        if (! $event) {
            $this->redirectToListPage();
        }

        $cache = $this->getCache();
        $talkApi = $this->getTalkApi();
        $scheduler = new EventScheduler($talkApi);

        $schedule = $scheduler->getScheduleData($event);

        $this->render('Event/schedule.html.twig', array(
            'event' => $event,
            'eventDays' => $schedule,
        ));
    }

    public function quicklink($stub)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByStub($stub);
        if (! $event) {
            $this->redirectToListPage();
        }

        $this->redirectToDetailPage($event->getUrlFriendlyName(), 301);
    }

    public function addComment($friendly_name)
    {
        $request = $this->application->request();
        $rating = (int) $request->post('rating');
        $comment = $request->post('comment');
        $url = $this->application->urlFor("event-detail", array('friendly_name' => $friendly_name));
        $url .= '#add-comment';

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);
        if ($event) {
            try {
                $eventApi->addComment($event, $rating, $comment);
            } catch (Exception $e) {
                if (stripos($e->getMessage(), 'duplicate comment') !== false) {
                    // duplicate comment
                    $this->application->flash('error', 'Duplicate comment.');
                    $this->application->redirect($url);
                }
                if (stripos($e->getMessage(), 'comment failed spam check') !== false) {
                    // spam comment
                    $this->application->flash('error', 'Comment failed the spam check.');
                    $this->application->redirect($url);
                }
                if (stripos($e->getMessage(), 'The field \"comment\" is required') !== false) {
                    // spam comment
                    $this->application->flash('error', 'You must provide a comment.');
                    $this->application->redirect($url);
                }
                throw $e;
            }
        }

        $this->application->flash('message', 'Thank you for your comment.');
        $this->application->redirect($url);
    }

    public function attend($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $eventApi->attend($event, $_SESSION['user']);
        }

        $friendlyUrl = $this->application->request()->get('r');
        if ($friendlyUrl) {
            $this->redirectToDetailPage($friendlyUrl);
        }

        $this->application->redirect('/');
    }

    public function unattend($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $eventApi->unattend($event, $_SESSION['user']);
        }

        $friendlyUrl = $this->application->request()->get('r');
        if ($friendlyUrl) {
            $this->redirectToDetailPage($friendlyUrl);
        }

        $this->application->redirect('/');
    }

    /**
     * Action used to display the form with which an event can be submitted and with which a form can be submitted.
     *
     * @return void
     */
    public function submit()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new EventFormType());

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $event = $this->addEventUsingForm($form);

                if ($event instanceof EventEntity) {
                    $this->redirectToDetailPage($event->getUrlFriendlyName());
                }
            }
        }

        $this->render(
            'Event/submit.html.twig',
            array(
                'form'      => $form->createView(),
                'timezones' => EventFormType::getNestedListOfTimezones(),
            )
        );
    }

    /**
     * Action used to display a form to edit an event and with which the form can be submitted
     *
     * @return void
     */
    public function edit($friendly_name)
    {
        $request = $this->application->request();

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            $this->redirectToListPage();
        }

        if (! $event->getCanEdit()) {
            $this->redirectToDetailPage($event->getUrlFriendlyName());
        }

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new EventFormType(), $event);
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $event = $this->editEventUsingForm($form, $event);

                if ($event instanceof EventEntity) {
                    $this->redirectToDetailPage($event->getUrlFriendlyName());
                }
            }
        }

        $this->render(
            'Event/edit.html.twig',
            array(
                'event'     => $event,
                'form'      => $form->createView(),
                'timezones' => EventFormType::getNestedListOfTimezones(),
            )
        );


    }

    /**


    }

    /**
     * Submits the form data to the API and returns the newly created event, false if there is an error or null
     * if it is held for moderation.
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return EventEntity|null|false
     */
    private function addEventUsingForm(Form $form)
    {
        $eventApi = $this->getEventApi();
        $values = $form->getData();

        $result = false;
        try {
            $result = $eventApi->submit($values);
        } catch (\Exception $e) {
            $form->addError(
                new FormError('An error occurred while submitting your event: ' . $e->getMessage())
            );
        }

        return $result;
    }

    /**
     * Submits the form data to the API and returns the edited event, false if there is an error or null
     * if it is held for moderation.
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return EventEntity|null|false
     */
    private function editEventUsingForm(Form $form)
    {
        $eventApi = $this->getEventApi();
        $values = $form->getData()->toArray();

        $result = false;
        try {
            $result = $eventApi->edit($values);
        } catch (\Exception $e) {
            $form->addError(
                new FormError('An error occurred while editing your event: ' . $e->getMessage())
            );
        }

        return $result;
    }

    protected function getEventApi()
    {
        $cache = $this->getCache();
        $eventDb = new EventDb($cache);
        $eventApi = new EventApi($this->cfg, $this->accessToken, $eventDb, $this->getUserApi());

        return $eventApi;
    }

    /**
     * Redirects the current request to the event listing page.
     *
     * @return void
     */
    private function redirectToListPage()
    {
        $this->application->redirect($this->application->urlFor("events-index"));
    }

    /**
     * Redirect the current request to the detail page with the given friendly name / stub.
     *
     * @param string  $friendlyName
     * @param integer $status
     *
     * @throws Stop request execution is directly ended by this method.
     *
     * @return void
     */
    private function redirectToDetailPage($friendlyName, $status = 302)
    {
        $this->application->redirect(
            $this->application->urlFor('event-detail', array('friendly_name' => $friendlyName)),
            $status
        );
    }

    public function xhrAttend($friendly_name)
    {
        $this->application->response()->header('Content-Type', 'application/json');

        $api = $this->getEventApi();
        $event = $api->getByFriendlyUrl($friendly_name);

        if ($event) {
            $result = $this->getEventApi()->attend($event, $_SESSION['user']);
        }

        $this->application->response()->body(json_encode(array('success' => $result)));
    }

    public function xhrUnattend($friendly_name)
    {
        $this->application->response()->header('Content-Type', 'application/json');

        $api = $this->getEventApi();
        $event = $api->getByFriendlyUrl($friendly_name);

        if ($event) {
            $result = $this->getEventApi()->unattend($event, $_SESSION['user']);
        }

        $this->application->response()->body(json_encode(array('success' => $result)));
    }

    /**
     * @param array $comments
     *
     * @return array
     */
    private function getTalkSlugsForTalkComments(array $comments, EventEntity $event)
    {
        $slugs = $this->getTalkSlugsFromDb($comments);

        // If we didn't get all slugs from cache, need to fetch from API
        if (in_array(null, $slugs)) {
            $slugs = $this->getTalkSlugsFromApi($event);
        }

        return $slugs;
    }

    /**
     * @param array $comments
     *
     * @return array
     */
    private function getTalkSlugsFromDb(array $comments)
    {
        $talkDb  = $this->getTalkDb();
        $slugs   = array();

        /** @var \Talk\TalkCommentEntity $comment */
        foreach ($comments as $comment) {
            $slugs[$comment->getTalkUri()] = $talkDb->getSlugFor($comment->getTalkUri());
        }

        return $slugs;
    }

    /**
     * @param array       $slugs
     * @param EventEntity $event
     */
    private function getTalkSlugsFromApi(EventEntity $event)
    {
        $talkApi = $this->getTalkApi();

        // Fetch talks from the API
        $talks = $talkApi->getCollection(
            $event->getTalksUri(),
            array('resultsperpage' => 100) // Make sure we get all talks with a single request
        );

        /** @var \Talk\TalkEntity $talk */
        foreach ($talks['talks'] as $talk) {
            $slugs[$talk->getApiUri()] = $talk->getUrlFriendlyTalkTitle();
        }

        return $slugs;
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
     * @return TalkDb
     */
    private function getTalkDb()
    {
        return new TalkDb($this->getCache());
    }

    /**
     * @return TalkApi
     */
    private function getTalkApi()
    {
        return new TalkApi($this->cfg, $this->accessToken, $this->getTalkDb(), $this->getUserApi());
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
