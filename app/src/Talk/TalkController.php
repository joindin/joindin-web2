<?php
namespace Talk;

use Application\BaseController;
use Application\CacheService;
use Event\EventEntity;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Event\EventDb;
use Event\EventApi;
use User\UserDb;
use User\UserApi;
use Exception;
use Slim\Slim;

class TalkController extends BaseController
{

    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->map('/event/:eventSlug/add-talk', array($this, 'add'))->via('GET', 'POST')->name('talk-add');
        $app->get('/event/:eventSlug/:talkSlug', array($this, 'index'))->name('talk');
        $app->post('/event/:eventSlug/:talkSlug/star', array($this, 'star'))->name('talk-star');
        $app->map('/event/:eventSlug/:talkSlug/edit', array($this, 'edit'))->via('GET', 'POST')->name('talk-edit');
        $app->get('/talk/:talkStub', array($this, 'quick'))->name('talk-quicklink');
        $app->post('/event/:eventSlug/:talkSlug/add-comment', array($this, 'addComment'))->name('talk-add-comment');
    }


    public function index($eventSlug, $talkSlug)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

        if (!$event) {
            return Slim::getInstance()->notFound();
        }

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            return Slim::getInstance()->notFound();
        }

        $comments = $talkApi->getComments($talk->getCommentUri(), true, 0);

        $this->render(
            'Talk/index.html.twig',
            array(
                'talk' => $talk,
                'event' => $event,
                'comments' => $comments,
                'talkSlug' => $talkSlug,
            )
        );
    }

    public function star($eventSlug, $talkSlug)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);
        $this->application->contentType('application/json');

        if (!$event) {
            $this->application->notFound();
            return;
        }

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            $this->application->notFound();
            return;
        }

        try {
            $result = $talkApi->toggleStar($talk);
        } catch (Exception $e) {
            $reason = $e->getMessage();
            $this->application->halt(500, '{ "message": "Failed to toggle star: ' . $reason .'" }');
        }
        
        $this->application->status(200);
        echo json_encode($result);
    }

    public function quick($talkStub)
    {
        $cache = $this->getCache();
        $talkDb = new TalkDb($cache);
        $talk = $talkDb->load('stub', $talkStub);

        $eventDb = new EventDb($cache);
        $event = $eventDb->load('uri', $talk['event_uri']);
        if (!$event) {
            return \Slim\Slim::getInstance()->notFound();
        }

        $this->application->redirect(
            $this->application->urlFor(
                'talk',
                array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug'])
            )
        );
    }

    public function addComment($eventSlug, $talkSlug)
    {
        $request = $this->application->request();
        $comment = trim(strip_tags($request->post('comment')));
        $rating = (int) $request->post('rating');
        $url = $this->application->urlFor("talk", array('eventSlug' => $eventSlug, 'talkSlug' => $talkSlug));

        if ($comment == '' || $rating == 0) {
            $this->application->flash('error', 'Please provide a comment and rating');
            $url .= '#add-comment';
            $this->application->redirect($url);
        }

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if ($talk) {
            try {
                $talkApi->addComment($talk, $rating, $comment);
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
                throw $e;
            }
        }

        $this->application->flash('message', 'Thank you for your comment.');
        $url .= '#add-comment';
        $this->application->redirect($url);
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
     * @return EventApi
     */
    private function getEventApi()
    {
        $eventDb = new EventDb($this->getCache());
        return new EventApi($this->cfg, $this->accessToken, $eventDb, $this->getUserApi());
    }

    /**
     * @return TalkApi
     */
    private function getTalkApi()
    {
        $talkDb = new TalkDb($this->getCache());
        return new TalkApi($this->cfg, $this->accessToken, $talkDb, $this->getUserApi());
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        $userDb = new UserDb($this->getCache());
        return new UserApi($this->cfg, $this->accessToken, $userDb);
    }

    /**
     * @param string $eventSlug
     */
    public function add($eventSlug)
    {
        $request = $this->application->request();

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

        if (!$event) {
            return Slim::getInstance()->notFound();
        }

        $class = new \stdClass();
        $class->talk_title = '';
        $class->url_friendly_talk_title = '';
        $class->talk_description = '';
        $class->type = "";
        $class->start_date = $event->getStartDate();
        $class->duration = 60;
        $class->stub = "";
        $class->average_rating = 0;
        $class->comments_enabled = 0;
        $class->comment_count = 0;
        $class->starred = false;
        $class->starred_count = 0;
        $class->speakers = [];
        $class->tracks = [];
        $class->uri = "";
        $class->language = 'English (US)';
        $class->slides_link = '';
        $talk = new TalkEntity($class);

        $factory = $this->application->formFactory;
        $form    = $factory->create(new TalkFormType($event->getTimezone()), $talk);
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $talk = $this->submitTalkUsingForm($form, $event);

                if ($talk instanceof TalkEntity) {
                    $this->redirectToDetailPage($event->getEventSlug(), $talk->getUrlFriendlyTalkTitle());
                }
            }
        }

        $this->render(
            'Talk/add.html.twig',
            array(
                'event' => $event,
                'talk'  => $talk,
                'form'  => $form->createView(),
            )
        );
    }


    /**
     * @param string $eventSlug
     * @param string $talkSlug
     */
    public function edit($eventSlug, $talkSlug)
    {
        $request = $this->application->request();

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());


        if (!$talk) {
            return Slim::getInstance()->notFound();
        }

        $factory = $this->application->formFactory;
        $form    = $factory->create(new TalkFormType($event->getTimezone()), $talk);
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                var_Dump($talk);
                $result = $this->editTalkUsingForm($form, $talk);

                if ($result === true) {
                    $this->redirectToDetailPage($event->getEventSlug(), $talk->getUrlFriendlyTalkTitle());
                }
            }
        }

        $this->render(
            'Talk/edit.html.twig',
            array(
                'event' => $event,
                'talk'  => $talk,
                'form'  => $form->createView(),
            )
        );
    }

    /**
     * Submits the form data to the API and returns the edited talk, false if there is an error
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return boolean
     */
    private function editTalkUsingForm(Form $form, TalkEntity $talk)
    {
        $talkApi = $this->getTalkApi();
        $values = $form->getData()->toArray();

        $result = false;
        try {
            $result = $talkApi->edit($values, $talk->getApiUri());
        } catch (\Exception $e) {
            $form->addError(
                new FormError(sprintf(
                    'An error occurred while editing your talk: %s',
                    $e->getMessage() . "\n" . $e->getTraceAsString()
                ))
            );
        }

        return $result;
    }

    /**
     * Alter the speakers
     *
     * @param array speakernames
     * @param TalkEntity $talk
     *
     * @throws \Exception
     * @return boolean
     */
    public function alterSpeakers($speakernames, TalkEntity $talk)
    {
        //$speakersApi = $this->get
    }

    /**
     * Submits the form data to the API and returns the edited talk, false if there is an error
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return TalkEntity|false
     */
    private function submitTalkUsingForm(Form $form, EventEntity $event)
    {
        $talkApi = $this->getTalkApi();
        $values = $form->getData()->toArray();

        $result = false;
        try {
            $result = $talkApi->submit($values, $event->getTalksUri());
        } catch (\Exception $e) {
            $form->addError(
                new FormError(sprintf(
                    'An error occurred while editing your talk: %s',
                    $e->getMessage() . "\n" . $e->getTraceAsString()
                ))
            );
        }

        return $result;
    }

    /**
     * Redirect the current request to the detail page with the given friendly name / stub.
     *
     * @param string  $eventSlug
     * @param string  $talkSlug
     * @param integer $status
     *
     * @throws Stop request execution is directly ended by this method.
     *
     * @return void
     */
    private function redirectToDetailPage($eventSlug, $talkSlug, $status = 302)
    {
        $this->application->redirect(
            $this->application->urlFor('talk', array('talkSlug' => $talkSlug, 'eventSlug' => $eventSlug)),
            $status
        );
    }
}
