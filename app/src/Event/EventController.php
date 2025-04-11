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
use Talk\TalkFormType;
use Talk\TalkTypeApi;
use User\UserDb;
use User\UserApi;
use Exception;
use Slim\Slim;
use Language\LanguageApi;

class EventController extends BaseController
{
    private $itemsPerPage = 10;

    private int $pendingItemsPerPage = 30;

    public function __construct(Slim $slim)
    {
        parent::__construct($slim);
    }

    protected function defineRoutes(Slim $slim): void
    {
        // named routes first; should an event pick the same name then at least our actions take precedence
        $slim->get('/event', [$this, 'index'])->name("events-index");
        $slim->get('/event/pending', [$this, 'pending'])->name("events-pending");
        $slim->map('/event/submit', [$this, 'submit'])->via('GET', 'POST')->name('event-submit');
        $slim->map('/event/:friendly_name/import', [$this, 'eventImportCsv'])->via('GET', 'POST')
                                                                            ->name("event-import-csv");
        $slim->get('/event/callforpapers', [$this, 'callForPapers'])->name('event-call-for-papers');
        $slim->get('/event/:friendly_name', [$this, 'eventDefault'])->name("event-default");
        $slim->get('/event/:friendly_name/details', [$this, 'details'])->name("event-detail");
        $slim->get('/event/:friendly_name/attendees', [$this, 'attendees'])->name("event-attendees");
        $slim->get('/event/:friendly_name/slides', [$this, 'slides'])->name("event-slides");
        $slim->get('/event/:friendly_name/comments', [$this, 'comments'])->name("event-comments");
        $slim->get('/event/:friendly_name/comments/:comment_hash/report', [$this, 'reportComment'])
            ->name("event-comments-reported");
        $slim->get('/event/:friendly_name/schedule', [$this, 'schedule'])->name("event-schedule");
        $slim->get('/event/:friendly_name/schedule/list(/:starred)', [$this, 'scheduleList'])
            ->name("event-schedule-list");
        $slim->get('/event/:friendly_name/schedule/grid(/:starred)', [$this, 'scheduleGrid'])
            ->name("event-schedule-grid");
        $slim->get('/event/:friendly_name/talk-comments', [$this, 'talkComments'])->name("event-talk-comments");
        $slim->post('/event/:friendly_name/add-comment', [$this, 'addComment'])->name('event-add-comment');
        $slim->map('/event/:friendly_name/edit', [$this, 'edit'])->via('GET', 'POST')->name('event-edit');
        $slim->get('/e/:stub', [$this, 'quicklink'])->name("event-quicklink");
        $slim->get('/event/xhr-attend/:friendly_name', [$this, 'xhrAttend']);
        $slim->get('/event/xhr-unattend/:friendly_name', [$this, 'xhrUnattend']);
        $slim->get('/event/attend/:friendly_name', [$this, 'attend'])->name("event-attend");
        $slim->get('/event/unattend/:friendly_name', [$this, 'unattend'])->name("event-unattend");
        $slim->post('/event/action-pending-event/:friendly_name', [$this, 'actionPendingEvent'])
            ->name("event-action-pending");
        $slim->get('/event/view/:eventId(/:extra+)', [$this, 'redirectFromId'])
            ->name('event-redirect-from-id')
            ->conditions(['eventId' => '\d+']);
        $slim->get('/event/:friendly_name/reported-comments', [$this, 'reportedComments'])
            ->name("event-reported-comments");
        $slim->post('/event/:friendly_name/moderate-comment', [$this, 'moderateComment'])
            ->name("event-moderate-comment");
        $slim->map('/event/:friendly_name/add-talk', [$this, 'addTalk'])->via('GET', 'POST')
            ->name("event-add-talk");
        $slim->map('/event/:friendly_name/edit-tracks', [$this, 'editTracks'])->via('GET', 'POST')
            ->name("event-edit-tracks");
        $slim->map('/event/:friendly_name/claims', [$this, 'talkClaims'])->via('GET', 'POST')
            ->name("event-talk-claims");
        $slim->map('/event/:friendly_name/host/:host_name/remove', [$this, 'removeHost'])->via('GET', 'POST')
            ->name('event-host-remove');
        $slim->map('/event/:friendly_name/host', [$this, 'addHost'])->via('GET', 'POST')
            ->name('event-hosts');
    }

    public function index(): void
    {
        // Start is... magical - in the API, if you leave start null, we get "upcoming".
        // If we default it to 0, we get all events, including old ones.
        // Don't 'fix' this one unless you're also fixing the API.
        $start = null;

        $page  = (int)$this->application->request()->get('page');

        if (array_key_exists('events_list_middle_start', $_SESSION) && $page !== 0) {
            // use the middle start point that we've remembered, unless it's page zero,
            // in which case, we reset in case new events have been added
            $start = $_SESSION['events_list_middle_start'] + ($page * $this->itemsPerPage);

            if ($start < 1) {
                $this->itemsPerPage = $start + $this->itemsPerPage;
                $start              = 1;
            }
        }

        $eventApi = $this->getEventApi();
        $events   = $eventApi->getEvents($this->itemsPerPage, $start, 'all');
        // Find out the start number that has been sent back to us by the API
        if ($start === null && isset($events['pagination'])) {
            parse_str(parse_url($events['pagination']->this_page, PHP_URL_QUERY), $parts);
            if (isset($parts['start'])) {
                $start = $parts['start'];
            }

            $_SESSION['events_list_middle_start'] = $start;
        }

        $cfpEvents = $eventApi->getEvents(4, 0, 'cfp', true);

        $this->render(
            'Event/index.html.twig',
            [
                'page'       => $page,
                'cfp_events' => $cfpEvents,
                'events'     => $events
            ]
        );
    }

    public function pending(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']->getAdmin() == false) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $this->application->urlFor('events-pending')
            );
        }

        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $start = ($page -1) * $this->pendingItemsPerPage;

        $eventApi = $this->getEventApi();
        $events   = $eventApi->getEvents(
            $this->pendingItemsPerPage,
            $start,
            'pending',
            true
        );

        $this->render(
            'Event/pending.html.twig',
            [
                'page'   => $page,
                'events' => $events
            ]
        );
    }

    public function callForPapers(): void
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $start = ($page -1) * $this->itemsPerPage;

        $eventApi = $this->getEventApi();
        $events   = $eventApi->getEvents(
            $this->itemsPerPage,
            $start,
            'cfp',
            true
        );

        $this->render(
            'Event/call-for-papers.html.twig',
            [
                'page'   => $page,
                'events' => $events,
            ]
        );
    }


    /**
     * Return default page for event
     *
     * If event in progress or within 2 days of finishing return schedule
     * Otherwise, return details
     *
     * @@see https://joindin.jira.com/browse/JOINDIN-609 If last page remembered default to that instead
     */
    public function eventDefault(string $friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            // Maybe it's a stub
            $event = $eventApi->getByStub($friendly_name);
            if ($event) {
                $this->redirectToDetailPage($event->getUrlFriendlyName(), 301);
            }

            return Slim::getInstance()->notFound();
        }

        $action = 'scheduleList';
        if (isset($_COOKIE['schedule-view']) && $_COOKIE['schedule-view'] == 'grid') {
            $action = 'scheduleGrid';
        }

        return $this->$action($friendly_name);
    }

    public function details(string $friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            return Slim::getInstance()->notFound();
        }

        $quicklink = $this->application->request()->headers("host")
            . $this->application->urlFor('event-quicklink', ['stub' => $event->getStub()]);

        $attendees  = $eventApi->getAttendees($event->getAttendeesUri(), 6, true);


        $this->render(
            'Event/details.html.twig',
            [
                'event'     => $event,
                'quicklink' => $quicklink,
                'attendees' => $attendees
            ]
        );
    }

    public function attendees(string $friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            return Slim::getInstance()->notFound();
        }


        $attendees  = $eventApi->getAttendees($event->getAttendeesUri(), 0, true);

        $this->render(
            'Event/_common/event_attendees.html.twig',
            [
                'event'     => $event,
                'fullList'  => true,
                'attendees' => $attendees
            ]
        );
    }

    public function comments(string $friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            return Slim::getInstance()->notFound();
        }

        $quicklink = $this->application->request()->headers("host")
            . $this->application->urlFor('event-quicklink', ['stub' => $event->getStub()]);

        $comments = $eventApi->getComments($event->getCommentsUri(), true);
        $this->render(
            'Event/comments.html.twig',
            [
                'event'     => $event,
                'quicklink' => $quicklink,
                'comments'  => $comments,
            ]
        );
    }

    public function talkComments(string $friendly_name): void
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');
        $start = ($page -1) * $this->itemsPerPage;

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $comments = $eventApi->getTalkComments(
                $event->getAllTalkCommentsUri(),
                $this->itemsPerPage,
                $start,
                true
            );

            // If we have comments, fetch talk slugs for the talks so that we can create links to them in the template
            $slugs = [];
            if (array_key_exists('comments', $comments) && $comments['pagination']->count > 0) {
                $slugs = $this->getTalkSlugsForTalkComments($comments['comments'], $event);
            }

            $this->render(
                'Event/talk-comments.html.twig',
                [
                    'event'        => $event,
                    'page'         => $page,
                    'talkComments' => $comments,
                    'talkSlugs'    => $slugs,
                ]
            );
        } else {
            $events_url = $this->application->urlFor("events-index");
            $this->application->redirect($events_url);
        }
    }

    public function schedule(string $friendly_name): void
    {
        $scheduleView = 'list';
        if (isset($_COOKIE['schedule-view']) && $_COOKIE['schedule-view'] == 'grid') {
            $scheduleView = 'grid';
        }

        $this->application->flashKeep();

        $events_url = $this->application->urlFor('event-schedule-' . $scheduleView, ['friendly_name' => $friendly_name]);
        $this->application->redirect($events_url);
    }

    public function slides(string $friendly_name): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            $this->redirectToListPage();
        }

        $agenda = $this->getTalkApi()->getAgenda($event->getTalksUri());

        $this->render('Event/slides.html.twig', [
            'event'  => $event,
            'agenda' => $agenda,
        ]);
    }

    public function scheduleList(string $friendly_name, $starred = false): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            $this->redirectToListPage();
        }

        setcookie('schedule-view', 'list', ['expires' => strtotime('+2 years'), 'path' => '/']);

        $agenda = $this->getTalkApi()->getAgenda($event->getTalksUri());

        $request                  = $this->application->request();
        $starredOnly              = ($starred === 'starred');
        $currentUrlWithoutStarred = str_replace('/starred', '', $request->getResourceUri());


        // Does it end in /schedule/list or are we on the default event page
        $expectedUrlSuffix = '/schedule/list';
        if (substr($currentUrlWithoutStarred, strlen($expectedUrlSuffix)*-1) !== $expectedUrlSuffix) {
            $currentUrlWithoutStarred .= $expectedUrlSuffix;
        }

        $this->render('Event/schedule-list.html.twig', [
            'event'        => $event,
            'agenda'       => $agenda,
            'starred'      => $starred,
            'starred_only' => $starredOnly,
            'current_url'  => $currentUrlWithoutStarred
        ]);
    }

    public function scheduleGrid(string $friendly_name, $starred = false): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            $this->redirectToListPage();
        }

        setcookie('schedule-view', 'grid', ['expires' => strtotime('+2 years'), 'path' => '/']);

        $talkApi        = $this->getTalkApi();
        $eventScheduler = new EventScheduler($talkApi);

        $schedule = $eventScheduler->getScheduleData($event);

        $request                  = $this->application->request();
        $starredOnly              = ($starred === 'starred');
        $currentUrlWithoutStarred = str_replace('/starred', '', $request->getResourceUri());

        // Does it end in /schedule/grid or are we on the default event page
        $expectedUrlSuffix = '/schedule/grid';
        if (substr($currentUrlWithoutStarred, strlen($expectedUrlSuffix)*-1) !== $expectedUrlSuffix) {
            $currentUrlWithoutStarred .= $expectedUrlSuffix;
        }

        $this->render('Event/schedule-grid.html.twig', [
            'event'        => $event,
            'eventDays'    => $schedule,
            'starred'      => $starred,
            'starred_only' => $starredOnly,
            'current_url'  => $currentUrlWithoutStarred
        ]);
    }

    public function quicklink($stub): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByStub($stub);
        if (! $event) {
            $this->redirectToListPage();
        }

        $this->redirectToDetailPage($event->getUrlFriendlyName(), 301);
    }

    public function addComment(string $friendly_name): void
    {
        $request = $this->application->request();
        $comment = $request->post('comment');
        $rating  = (int) $request->post('rating');
        $url     = $this->application->urlFor("event-comments", ['friendly_name' => $friendly_name]);
        $url .= '#add-comment';

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if ($event) {
            try {
                $eventApi->addComment($event, $comment, $rating);
            } catch (Exception $exception) {
                if (stripos($exception->getMessage(), 'duplicate comment') !== false) {
                    // duplicate comment
                    $this->application->flash('error', 'Duplicate comment.');
                    $this->application->redirect($url);
                }

                if (stripos($exception->getMessage(), 'comment failed spam check') !== false) {
                    // spam comment
                    $this->application->flash('error', 'Comment failed the spam check.');
                    $this->application->redirect($url);
                }

                if (stripos($exception->getMessage(), 'The field \"comment\" is required') !== false) {
                    // spam comment
                    $this->application->flash('error', 'You must provide a comment.');
                    $this->application->redirect($url);
                }

                throw $exception;
            }
        }

        $this->application->flash('message', 'Thank you for your comment.');
        $this->application->redirect($url);
    }

    public function reportComment(string $friendly_name, $comment_hash): void
    {
        $eventApi        = $this->getEventApi();
        $event           = $eventApi->getByFriendlyUrl($friendly_name);
        $reportedComment = null;
        $url             = $this->application->urlFor("event-comments", ['friendly_name' => $friendly_name]);

        $comments = $eventApi->getComments($event->getCommentsUri());
        foreach ($comments as $comment) {
            if ($comment->getCommentHash() !== $comment_hash) {
                continue;
            }

            $reportedComment = $comment;
            break;
        }

        if (!isset($reportedComment)) {
            $this->application->flash('error', 'The reported comment was not found on this event.');
            $this->application->redirect($url);
        }

        try {
            $eventApi->reportComment($reportedComment->getReportedUri());
        } catch (Exception $exception) {
            $this->application->flash('error', $exception->getMessage());
            $this->application->redirect($url);
        }

        $this->application->flash('message', 'Thank you for your report.');
        $this->application->redirect($url);
    }

    public function attend(string $friendly_name): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $eventApi->attend($event);
        }

        $friendlyUrl = $this->application->request()->get('r');
        if ($friendlyUrl) {
            $this->redirectToDetailPage($friendlyUrl);
        }

        $this->application->redirect('/');
    }

    public function unattend(string $friendly_name): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $eventApi->unattend($event);
        }

        $friendlyUrl = $this->application->request()->get('r');
        if ($friendlyUrl) {
            $this->redirectToDetailPage($friendlyUrl);
        }

        $this->application->redirect('/');
    }

    /**
     * Action used to display the form with which an event can be submitted and with which a form can be submitted.
     */
    public function submit(): void
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

                // held for moderation - note that we test for null explicitly as false means
                // that we failed to submit the event to the API
                if ($event === null) {
                    $this->application->flash('message', "Thank you for your submission.\n"
                        . "If your event is approved, you will receive an email letting you know it's been accepted.");
                    $this->redirectToListPage();
                }
            }
        }

        $this->render(
            'Event/submit.html.twig',
            [
                'form'      => $form->createView(),
                'timezones' => EventFormType::getNestedListOfTimezones(),
            ]
        );
    }

    /**
     * Action used to display a form to edit an event and with which the form can be submitted
     */
    public function edit(string $friendly_name): void
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
                $result = $this->editEventUsingForm($form);
                if ($result instanceof EventEntity) {
                    $this->redirectToDetailPage($result->getUrlFriendlyName());
                }
            }
        }

        $this->render(
            'Event/edit.html.twig',
            [
                'event'     => $event,
                'form'      => $form->createView(),
                'timezones' => EventFormType::getNestedListOfTimezones(),
            ]
        );
    }

    /**
     * Approve or reject a pending event
     */
    public function actionPendingEvent(string $friendly_name): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']->getAdmin() == false) {
            $this->application->redirect($this->application->urlFor('not-allowed'));
        }

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            $this->application->flash('error', 'Could not find event.');
            $this->application->redirect($this->application->urlFor("events-pending"));
        }

        try {
            $action = $this->application->request->post('action', '');
            switch ($action) {
                case 'approve':
                    $eventApi->approveEvent($event->getApprovalUri());
                    $this->application->flash('message', 'Event approved.');
                    break;

                case 'reject':
                    $eventApi->rejectEvent($event->getApprovalUri());
                    $this->application->flash('message', 'Event rejected.');
                    break;
            }
        } catch (Exception $exception) {
            $this->application->flash('error', $exception->getMessage());
        }

        $this->application->redirect($this->application->urlFor("events-pending"));
    }

    /**
     * Handles redirecting web1 event urls to web2
     * e.g. /event/view/3 -> /event/myevent
     */
    public function redirectFromId(int $eventId, $extra = false)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getEventById($eventId);
        if (!$event instanceof \Event\EventEntity) {
            return Slim::getInstance()->notFound();
        }

        if ($extra && is_array($extra) && ($extra[0] == "talk_comments")) {
            $this->application->redirect(
                $this->application->urlFor(
                    'event-talk-comments',
                    ['friendly_name' => $event->getUrlFriendlyName()]
                )
            );
        }

        if ($extra && is_array($extra) && ($extra[0] == "comments")) {
            $this->application->redirect(
                $this->application->urlFor(
                    'event-comments',
                    ['friendly_name' => $event->getUrlFriendlyName()]
                )
            );
        }

        if ($extra && is_array($extra) && ($extra[0] == "talks")) {
            $this->application->redirect(
                $this->application->urlFor(
                    'event-schedule',
                    ['friendly_name' => $event->getUrlFriendlyName()]
                )
            );
        } else {
            $this->application->redirect(
                $this->application->urlFor(
                    'event-default',
                    ['friendly_name' => $event->getUrlFriendlyName()]
                )
            );
        }
    }

    /**
     * Submits the form data to the API and returns the newly created event, false if there is an error or null
     * if it is held for moderation.
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     *
     * @return EventEntity|null|false
     */
    private function addEventUsingForm(Form $form)
    {
        $eventApi = $this->getEventApi();
        $values   = $form->getData();

        $result = false;
        try {
            $result = $eventApi->submit($values);
        } catch (\Exception $exception) {
            $form->addError(
                new FormError('an error occurred while submitting your event: ' . $exception->getMessage())
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
     *
     * @return EventEntity|null|false
     */
    private function editEventUsingForm(Form $form)
    {
        $eventApi = $this->getEventApi();
        $values   = $form->getData()->toArray();

        $result = false;
        try {
            $result = $eventApi->edit($values);
        } catch (\Exception $exception) {
            $form->addError(
                new FormError('An error occurred while editing your event: ' . $exception->getMessage())
            );
        }

        try {
            if (isset($_FILES['event']['error']['new_icon'])
                && $_FILES['event']['error']['new_icon'] == UPLOAD_ERR_OK) {
                $eventApi->uploadIcon(
                    $result->getImagesUri(),
                    $_FILES['event']['tmp_name']['new_icon']
                );
            }
        } catch (\Exception $exception) {
            $result   = false;
            $error    = $exception->getMessage();
            $messages = json_decode($error);
            if ($messages) {
                $error = implode(', ', $messages);
            }

            $form->addError(
                new FormError('An error occurred while uploading your event icon: ' . $error)
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
    private function editEventHostUsingForm(EventEntity $eventEntity, $hostUsername)
    {
        $eventApi = $this->getEventApi();

        $values   = [
            'hosts_uri' => $eventEntity->getHostsUri(),
            'host'      => $hostUsername,
        ];

        return $eventApi->editHost($values);
    }

    protected function getEventApi(): \Event\EventApi
    {
        $cacheService = $this->getCache();
        $eventDb      = new EventDb($cacheService);

        return new EventApi($this->cfg, $this->accessToken, $eventDb, $this->getUserApi());
    }

    protected function getLanguageApi(): \Language\LanguageApi
    {
        return new LanguageApi($this->cfg, $this->accessToken);
    }

    protected function getTalkTypeApi(): \Talk\TalkTypeApi
    {
        return new TalkTypeApi($this->cfg, $this->accessToken);
    }

    protected function getTrackApi(): \Event\TrackApi
    {
        return new TrackApi($this->cfg, $this->accessToken);
    }

    /**
     * Redirects the current request to the event listing page.
     */
    private function redirectToListPage(): void
    {
        $this->application->redirect($this->application->urlFor("events-index"));
    }

    /**
     * Redirect the current request to the detail page with the given friendly name / stub.
     *
     *
     * @throws Stop request execution is directly ended by this method.
     *
     */
    private function redirectToDetailPage(string $friendlyName, int $status = 302): void
    {
        $this->application->redirect(
            $this->application->urlFor('event-detail', ['friendly_name' => $friendlyName]),
            $status
        );
    }

    public function xhrAttend(string $friendly_name): void
    {
        $this->application->response()->header('Content-Type', 'application/json');

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        $result = null;
        if ($event) {
            $result = $this->getEventApi()->attend($event);
        }

        $this->application->response()->body(json_encode(['success' => $result]));
    }

    public function xhrUnattend(string $friendly_name): void
    {
        $this->application->response()->header('Content-Type', 'application/json');

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        $result = null;
        if ($event) {
            $result = $this->getEventApi()->unattend($event);
        }

        $this->application->response()->body(json_encode(['success' => $result]));
    }

    public function reportedComments(string $friendly_name): void
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            if (! $event->getCanEdit()) {
                $this->redirectToDetailPage($event->getUrlFriendlyName());
            }

            $eventComments = $eventApi->getReportedEventComments(
                $event->getReportedEventCommentsUri()
            );

            $talkComments = $eventApi->getReportedTalkComments(
                $event->getReportedTalkCommentsUri()
            );

            $this->render(
                'Event/reported-comments.html.twig',
                [
                    'event'         => $event,
                    'eventComments' => $eventComments,
                    'talkComments'  => $talkComments,
                ]
            );
        } else {
            $events_url = $this->application->urlFor("events-index");
            $this->application->redirect($events_url);
        }
    }

    /**
     * Moderate a comment by POSTing to this action with a decision and a
     * reported_uri. You must be logged in and an event admin to moderate
     * a comment. Redirects back to the list of reported comments.
     */
    public function moderateComment(string $friendly_name): void
    {
        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $this->application->urlFor('events-pending')
            );
        }

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            if (! $event->getCanEdit()) {
                $this->redirectToDetailPage($event->getUrlFriendlyName());
            }

            $reported_uri = $this->application->request->post('reported_uri');
            $decision     = $this->application->request->post('decision');

            $eventApi->moderateComment($reported_uri, $decision);
            if ($decision == 'approved') {
                $this->application->flash('message', 'Report accepted.');
            } else {
                $this->application->flash('message', 'Report rejected. Comment has been republished.');
            }
        }

        $url = $this->application->urlFor("event-reported-comments", ['friendly_name' => $friendly_name]);
        $this->application->redirect($url);
    }

    public function talkClaims(string $friendly_name): void
    {
        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect='
                . $this->application->urlFor('event-talk-claims', ['friendly_name' => $friendly_name])
            );
        }


        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            if (!$event->getCanEdit()) {
                $this->redirectToDetailPage($event->getUrlFriendlyName());
            }

            $claims_uri = $event->getPendingClaimsUri();

            $claims = $eventApi->getPendingClaims($claims_uri, true);

            $userApi = $this->getUserApi();
            $talkApi = $this->getTalkApi();

            foreach ($claims as &$claim) {
                $claim->user = $userApi->getUser($claim->speaker_uri);
                $claim->talk = $talkApi->getTalk($claim->talk_uri);
                $action      = $this->application->request->post('action');

                if ($this->application->request->post('display_name')
                    && $this->application->request->post('display_name') == $claim->display_name
                    && $this->application->request->post('username') == $claim->user->getUsername()
                    && $this->application->request->post("talk") == $claim->talk->getStub()
                ) {
                    $data = [
                        'display_name'  => $this->application->request->post('display_name'),
                        'username'      => $this->application->request->post('username'),
                    ];

                    if ($action == "approve") {
                        $this->appoveClaimPendingTalk($talkApi, $claim, $data);
                    } elseif ($action == "reject") {
                        $this->rejectClaimPendingTalk($talkApi, $claim, $data);
                    }
                }
            }

            $this->render(
                'Event/pending-claims.html.twig',
                [
                    'event'  => $event,
                    'claims' => $claims,
                ]
            );
        }
    }

    public function removeHost(string $friendly_name, $host_name): void
    {
        $this->application->request();

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            $this->redirectToListPage();
        }

        $userApi = $this->getUserApi();
        $user    = $userApi->getUserByUsername($host_name);

        $data = [
            'hosts_uri' => $event->getHostsUri(),
            'host'      => $user->getId(),
        ];

        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect='
                . $this->application->urlFor('event-hosts', ['friendly_name' => $friendly_name])
            );
        }

        try {
            $eventApi->removeHost($data);
            $this->application->flash(
                'message',
                sprintf(
                    '<strong>%1$s</strong> was removed as host for this event. Make sure they know about that!',
                    $user->getFullName()
                )
            );
            $this->application->redirect(
                $this->application->urlFor('event-hosts', ['friendly_name' => $friendly_name]),
                204
            );
        } catch (Exception $exception) {
            $this->application->flash(
                'error',
                $exception->getMessage()
            );
        }

        $this->application->redirect(
            $this->application->urlFor('event-hosts', ['friendly_name' => $friendly_name]),
        );
    }

    public function addHost(string $friendly_name): void
    {
        $request = $this->application->request();

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            $this->redirectToListPage();
        }

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new EventHostFormType(), array_merge($event->toArray(), [
            'host' => '',
        ]));
        if ($request->isPost()) {
            if (!isset($_SESSION['user'])) {
                $this->application->redirect(
                    $this->application->urlFor('not-allowed') . '?redirect='
                    . $this->application->urlFor('event-hosts', ['friendly_name' => $friendly_name])
                );
            }

            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getData();
                try {
                    $result = $this->editEventHostUsingForm($event, $values['host']);
                    if ($result instanceof EventEntity) {
                        $userApi = $this->getUserApi();
                        $user    = $userApi->getUserByUsername($values['host']);
                        $this->application->flash(
                            'message',
                            sprintf(
                                '<strong>%1$s</strong> was added as host. Make sure they know about their new role!',
                                $user->getFullName()
                            )
                        );

                        $this->application->redirect(
                            $this->application->urlFor('event-hosts', ['friendly_name' => $friendly_name]),
                            204
                        );
                    }
                } catch (Exception $e) {
                    $this->application->flash(
                        'error',
                        $e->getMessage()
                    );
                    $this->application->redirect(
                        $this->application->urlFor('event-hosts', ['friendly_name' => $friendly_name]),
                        204
                    );
                }
            }
        }

        $this->render(
            'Event/eventhosts.html.twig',
            [
                'event'     => $event,
                'form'      => $event->getCanEdit() ? $form->createView(): '',
            ]
        );
    }

    private function appoveClaimPendingTalk($talkApi, $claim, array $data): void
    {
        $talkApi->claimTalk($claim->approve_claim_uri, $data);

        $claim->approved = 1;
    }

    /**
     * Reject a talk claim
     */
    private function rejectClaimPendingTalk($talkApi, $claim, array $data): void
    {
        $talkApi->rejectTalkClaim($claim->approve_claim_uri, $data);

        $claim->approved = 0;
    }

    /**
     * Add a talk to the event
     */
    public function addTalk(string $friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (!$event) {
            return Slim::getInstance()->notFound();
        }

        if (!$event->getCanEdit()) {
            $this->application->flash('error', "You do not have permission to do this.");
            $this->redirectToDetailPage($event->getUrlFriendlyName());
        }

        $languageApi = $this->getLanguageApi();
        $languages   = $languageApi->getLanguagesChoiceList();

        $talkTypeApi = $this->getTalkTypeApi();
        $talkTypes   = $talkTypeApi->getTalkTypesChoiceList();

        $trackApi = $this->getTrackApi();
        $tracks   = $trackApi->getTracksChoiceList($event->getTracksUri());

        // default values
        $sessionKeys = ['duration', 'language', 'type', 'track'];
        foreach ($sessionKeys as $key) {
            $data[$key] = $this->getSessionVariable('add_talk_' . $key);
        }

        $data['speakers'][] = [];

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new TalkFormType($event, $languages, $talkTypes, $tracks), $data);

        $request = $this->application->request();
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getdata();

                // store some values to session for next form
                foreach ($sessionKeys as $sessionKey) {
                    $_SESSION['add_talk_' . $sessionKey] = $values[$sessionKey];
                }

                try {
                    $talkApi = $this->getTalkApi();
                    $talk    = $talkApi->addTalk($event->getTalksUri(), $values);

                    if (!empty($values['track']) && isset($tracks[$values['track']])) {
                        $talkApi->addTalkToTrack($talk->getTracksUri(), $values['track']);
                    }

                    $this->application->flash('message', "Talk added");
                    $this->application->redirect(
                        $this->application->urlFor('event-schedule', ['friendly_name' => $event->getUrlFriendlyName()])
                    );
                } catch (\Exception $e) {
                    $form->adderror(
                        new formError('An error occurred while adding this talk: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Event/add-talk.html.twig',
            [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * Edit tracks for this event
     */
    public function editTracks(string $friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (!$event) {
            return Slim::getInstance()->notFound();
        }

        if (!$event->getCanEdit()) {
            $this->application->flash('error', "You do not have permission to do this.");
            $this->redirectToDetailPage($event->getUrlFriendlyName());
        }

        $trackApi       = $this->getTrackApi();
        $tracks         = $trackApi->getTracks($event->getTracksUri());
        $numberOfTracks = 0;
        if ($tracks && $tracks['meta']['count']) {
            $data['tracks'] = $tracks['tracks'];
            $numberOfTracks = count($data['tracks']);
        } else {
            $data['tracks'][] = [];
        }

        $factory = $this->application->formFactory;
        $form    = $factory->create(new TrackCollectionFormType(), $data);

        $request = $this->application->request();
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getdata();

                try {
                    $eventTracksUri   = $event->getTracksUri();
                    $updatedTrackUris = [];
                    foreach ($values['tracks'] as $item) {
                        if ($item['uri']) {
                            $updatedTrackUris[$item['uri']] = $item['uri'];
                            $trackApi->updateTrack($item['uri'], $item);
                        } else {
                            $trackApi->addTrack($eventTracksUri, $item);
                        }
                    }

                    // have any tracks been removed from the form and so need to be deleted?
                    if ($numberOfTracks > 0 && $numberOfTracks != count($updatedTrackUris)) {
                        foreach ($data['tracks'] as $item) {
                            if (!isset($updatedTrackUris[$item['uri']])) {
                                $trackApi->deleteTrack($item['uri']);
                            }
                        }
                    }

                    $this->application->flash('message', "Tracks updated");
                    $this->application->redirect(
                        $this->application->urlFor(
                            'event-edit-tracks',
                            ['friendly_name' => $event->getUrlFriendlyName()]
                        )
                    );
                } catch (\Exception $e) {
                    $form->adderror(
                        new formError('An error occurred while adding this talk: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Event/edit-tracks.html.twig',
            [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * Upload Data from CSV for this event
     * @todo Validate & Process uploaded cSV
     */
    public function eventImportCsv(string $eventSlug): void
    {
        $this->application->config('oauth');
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new EventImportFormType());

        //use EventFormImportType to validate the CSV is valid
        //We possibly want to do some extensive data checking
        //BEFORE we blindly throw data from here to the API
        if ($request->isPost()) {
            try {
                if (isset($_FILES['event_import']['error']['csv_file'])
                    && $_FILES['event_import']['error']['csv_file'] == UPLOAD_ERR_OK) {
                    $eventApi = $this->getEventApi();
                    $event    = $eventApi->getByFriendlyUrl($eventSlug);
                    $handle   = fopen($_FILES['event_import']['tmp_name']['csv_file'], "r");

                    while (!feof($handle)) {
                        $talk       = fgetcsv($handle);
                        $date_start = new \DateTimeImmutable(
                            filter_var($talk[3], FILTER_SANITIZE_STRING)
                            . ' ' . filter_var(
                                $talk[4],
                                FILTER_SANITIZE_STRING
                            )
                        );
                        $speakers   = explode("|", $talk[2]);

                        foreach ($speakers as $key => $speaker) {
                            $speakers[$key] = filter_var($speaker, FILTER_SANITIZE_STRING);
                        }

                        $talk_data = [
                            'talk_title'       => filter_var($talk[0], FILTER_SANITIZE_STRING),
                            'talk_description' => filter_var($talk[1], FILTER_SANITIZE_STRING),
                            'type'             => filter_var($talk[7], FILTER_SANITIZE_STRING),
                            'track'            => filter_var($talk[8], FILTER_SANITIZE_STRING),
                            'language'         => filter_var($talk[6], FILTER_SANITIZE_STRING),
                            'start_date'       => $date_start->format('Y-m-d H:i'),
                            'speakers'         => $speakers,
                            'duration'         => filter_var($talk[5], FILTER_SANITIZE_STRING),
                        ];

                        $talk_api = $this->getTalkApi();
                        $talk_api->addTalk($event->getTalksUri(), $talk_data);
                    }

                    fclose($handle);
                }
            } catch (\Exception $e) {
                $error    = $e->getMessage();
                $messages = json_decode($error);
                if ($messages) {
                    $error = implode(', ', $messages);
                }

                $form->addError(
                    new FormError('An error occurred while uploading your event csv: ' . $error)
                );
            }
        }

        $this->render('Event/import-csv.html.twig', ['form' => $form->createView()]);
    }


    private function getTalkSlugsForTalkComments(array $comments, EventEntity $eventEntity): array
    {
        $slugs = $this->getTalkSlugsFromDb($comments);

        // If we didn't get all slugs from cache, need to fetch from API
        if (in_array(null, $slugs)) {
            return $this->getTalkSlugsFromApi($eventEntity);
        }

        return $slugs;
    }


    private function getTalkSlugsFromDb(array $comments): array
    {
        $talkDb  = $this->getTalkDb();
        $slugs   = [];

        /** @var \Talk\TalkCommentEntity $comment */
        foreach ($comments as $comment) {
            $slugs[$comment->getTalkUri()] = $talkDb->getSlugFor($comment->getTalkUri());
        }

        return $slugs;
    }

    /**
     * @return mixed[]
     */
    private function getTalkSlugsFromApi(EventEntity $eventEntity): array
    {
        $talkApi = $this->getTalkApi();

        // Fetch talks from the API
        $talks = $talkApi->getCollection(
            $eventEntity->getTalksUri(),
            ['resultsperpage' => 100] // Make sure we get all talks with a single request
        );

        $slugs = [];
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
        return $this->application->container->get(CacheService::class);
    }

    /**
     * @return TalkDb
     */
    private function getTalkDb()
    {
        return $this->application->container->get(TalkDb::class);
    }

    /**
     * @return TalkApi
     */
    private function getTalkApi()
    {
        return $this->application->container->get(TalkApi::class);
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        return $this->application->container->get(UserApi::class);
    }
}
