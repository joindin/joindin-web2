<?php
namespace Event;

use Application\BaseController;
use Joindin\Api\Entity\Event;
use Slim\Exception\Stop;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator;

class EventController extends BaseController
{
    private $eventsToShow = 10;

    protected function defineRoutes(\Slim\Slim $app)
    {
        // named routes first; should an event pick the same name then at least our actions take precedence
        $app->get('/event', array($this, 'index'))->name("events-index");
        $app->map('/events/submit', array($this, 'submit'))->via('GET', 'POST')->name('event-submit');
        $app->get('/event/attend/:friendly_name', array($this, 'attend'))->name("event-attend");

        $app->get('/event/:friendly_name', array($this, 'details'))->name("event-detail");
        $app->get('/event/:friendly_name/map', array($this, 'map'))->name("event-map");
        $app->get('/event/:friendly_name/schedule', array($this, 'schedule'))->name("event-schedule");
        $app->post('/event/:friendly_name/add-comment', array($this, 'addComment'))->name('event-add-comment');
        $app->get('/e/:stub', array($this, 'quicklink'))->name("event-quicklink");
    }

    public function index()
    {
        $page  = $this->application->request()->get('page', 1);
        $start = ($page - 1) * $this->eventsToShow;

        $eventApi = $this->getEventApi();
        $events = $eventApi->getCollection($this->eventsToShow, $start, 'upcoming');

        $this->render('Event/index.html.twig', array('page' => $page, 'events' => $events));
    }

    public function details($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);
        if (! $event) {
            $this->redirectToListPage();
        }

        $currentHost   = $this->application->request()->headers('host');
        $quicklinkPath = $this->application->urlFor('event-quicklink', array('stub' => $event->getStub()));
        $quicklinkUrl  = $currentHost . $quicklinkPath;

        $comments = $eventApi->getComments($event->getCommentsUri(), true);
        $this->render(
            'Event/details.html.twig',
            array(
                'event'     => $event,
                'quicklink' => $quicklinkUrl,
                'comments'  => $comments,
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

        $this->render('Event/map.html.twig', array('event' => $event));
    }

    public function schedule($friendly_name)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if (! $event) {
            $this->redirectToListPage();
        }

        $schedule = $this->getEventScheduler()->getScheduleData($event);

        $this->render('Event/schedule.html.twig', array('event' => $event, 'eventDays' => $schedule));
    }

    public function quicklink($stub)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByStub($stub);
        if (! $event) {
            $this->redirectToListPage();
        }

        $this->redirectToDetailPage($event->getUrlFriendlyName(), 301);
    }

    public function addComment($friendly_name)
    {
        $request = $this->application->request();
        $comment = $request->post('comment');

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($friendly_name);

        if ($event) {
            $eventApi->addComment($event, $comment);
        }

        $this->redirectToDetailPage($friendly_name);
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

                if ($event instanceof Event) {
                    $this->redirectToDetailPage($event->getUrlFriendlyName());
                }

                // held for moderation
                if ($event === null) {
                    $this->redirectToListPage();
                }
            }
        }

        $this->render('Event/submit.html.twig', array( 'form' => $form->createView()));
    }

    /**
     * Submits the form data to the API and returns the newly created event, false if there is an error or null
     * if it is held for moderation.
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return Event|null|false
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

    /**
     * Returns the service used to talk to the API for events.
     *
     * @return EventApi
     */
    protected function getEventApi()
    {
        return $this->application->container->get(ServiceProvider::SERVICE_API_EVENT);
    }

    /**
     * Returns a service to construct the event schedule with.
     *
     * @return EventScheduler
     */
    private function getEventScheduler()
    {
        return $this->application->container->get(ServiceProvider::SERVICE_SCHEDULER);
    }
}
