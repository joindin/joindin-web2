<?php
namespace User;

use Application\BaseController;
use Application\CacheService;
use Symfony\Component\Form\FormError;
use Slim\Slim;
use Talk\TalkDb;
use Talk\TalkApi;
use Event\EventDb;
use Event\EventApi;

class UserController extends BaseController
{
    /**
     * Routes implemented by this class
     *
     * @param \Slim $app Slim application instance
     *
     * @return void
     */
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/user/logout', array($this, 'logout'))->name('user-logout');
        $app->map('/user/login', array($this, 'login'))->via('GET', 'POST')->name('user-login');
        $app->map('/user/register', array($this, 'register'))->via('GET', 'POST')->name('user-register');
        $app->get('/user/verification', array($this, 'verification'))->name('user-verification');
        $app->map('/user/resend-verification', array($this, 'resendVerification'))
            ->via('GET', 'POST')->name('user-resend-verification');
        $app->get('/user/:username', array($this, 'profile'))->name('user-profile');
        $app->get('/user/:username/talks', array($this, 'profileTalks'))->name('user-profile-talks');
        $app->get('/user/:username/events', array($this, 'profileEvents'))->name('user-profile-events');
        $app->get('/user/:username/hosted', array($this, 'profileHosted'))->name('user-profile-hosted');
        $app->get('/user/:username/comments', array($this, 'profileComments'))->name('user-profile-comments');
    }

    /**
     * Login page
     *
     * @return void
     */
    public function login()
    {
        $config = $this->application->config('oauth');
        $request = $this->application->request();

        $error = false;
        if ($request->isPost()) {
            // handle submission of login form
        
            // make a call to the api with granttype=password
            $username = $request->post('username');
            $password = $request->post('password');
            $redirect = $request->post('redirect');
            $clientId = $config['client_id'];
            $clientSecret = $config['client_secret'];

            $authApi = new AuthApi($this->cfg, $this->accessToken);
            $result = $authApi->login($username, $password, $clientId, $clientSecret);

            if (false === $result) {
                $this->application->flash('error', "Failed to log in");
                if (empty($redirect)) {
                    $redirect = '/user/login';
                }
                $this->application->redirect($redirect);
            } else {
                session_regenerate_id(true);
                $_SESSION['access_token'] = $result->access_token;
                $this->accessToken = $_SESSION['access_token'];

                // now get users details
                $userApi = $this->getUserApi();
                $user = $userApi->getUser($result->user_uri);
                if ($user) {
                    $_SESSION['user'] = $user;
                    if (empty($redirect) || strpos($redirect, '/user/login') === 0) {
                        $this->application->redirect('/');
                    } else {
                        $this->application->redirect($redirect);
                    }
                } else {
                    unset($_SESSION['access_token']);
                }
            }
        }

        $this->render('User/login.html.twig');
    }

    /**
     * Registration page
     *
     * @return void
     */
    public function register()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new RegisterFormType());

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $success = $this->registerUserUsingForm($form);

                if ($success) {
                    $this->application->flash(
                        'message',
                        "User created successfully. Please check your email to verify your account before logging in"
                    );
                    $this->application->redirect('/');
                }
            }
        }

        $this->render(
            'User/register.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Submits the form data to the API and returns info for use by register()!
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function registerUserUsingForm($form)
    {
        $values = $form->getData();
        $userApi = $this->getUserApi();

        $result = false;
        try {
            $result = $userApi->register($values);
        } catch (\Exception $e) {
            $form->addError(
                new FormError('An error occurred while registering you: ' . $e->getMessage())
            );
        }

        return $result;
    }

    /**
     * Log out
     *
     * @return void
     */
    public function logout()
    {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        if (isset($_SESSION['access_token'])) {
            unset($_SESSION['access_token']);
        }
        session_regenerate_id(true);
        $this->application->redirect('/');
    }

    /**
     * Accept a user's email verification
     *
     * @return void
     */
    public function verification()
    {
        $request = $this->application->request();

        $token = $request->get('token');
        $userApi = $this->getUserApi();

        $result = false;
        try {
            $result = $userApi->verify($token);
            $this->application->flash('message', "Thank you for verifying your email address. You can now log in.");
        } catch (\Exception $e) {
            $this->application->flash('error', "Sorry, your verification link was invalid.");
        }

        $this->application->redirect('/user/login');

    }

    public function resendVerification()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new EmailVerificationFormType());

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getData();
                $email = $values['email'];

                $userApi = $this->getUserApi();

                $result = false;
                try {
                    $result = $userApi->reverify($email);
                    if ($result) {
                        $this->application->flash(
                            'message',
                            'We have resent your welcome email. Please check your email to verify your account before logging in.'
                        );
                        $this->application->redirect('/user/login');
                    }
                } catch (\Exception $e) {
                    $form->addError(
                        new FormError('An error occurred: ' . $e->getMessage())
                    );
                }

            }
        }

        $this->render(
            'User/emailverification.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * User profile page
     *
     * @param  string $username User's username
     * @return void
     */
    public function profile($username)
    {
        $userApi = $this->getUserApi();
        $user = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $talkDb = $this->getTalkDb();
        $talkApi = $this->getTalkApi();
        $eventApi = $this->getEventApi();

        $eventInfo = array(); // look up an event's name and url_friendly_name from its uri
        $talkInfo = array(); // look up a talk's url_friendly_talk_title from its uri

        $talkCollection = $talkApi->getCollection($user->getTalksUri(), ['verbose' => 'yes', 'resultsperpage' => 5]);
        $talks = false;
        if (isset($talkCollection['talks'])) {
            $talks = $talkCollection['talks'];
            foreach ($talks as $talk) {
                // look up event's name & url_friendly_name from the DB orAPI
                if (!isset($eventInfo[$talk->getApiUri()])) {
                    $eventInfo[$talk->getApiUri()] = $this->lookupEventInfo($talk->getEventUri());
                }
            }
        }

        $eventsCollection = $eventApi->getCollection(
            $user->getAttendedEventsUri(),
            ['verbose' => 'yes', 'resultsperpage' => 5]
        );
        $events = false;
        if (isset($eventsCollection['events'])) {
            $events = $eventsCollection['events'];
        }

        $hostedEventsCollection = $eventApi->getCollection(
            $user->getHostedEventsUri(),
            ['verbose' => 'yes', 'resultsperpage' => 5]
        );
        $hostedEvents = false;
        if (isset($hostedEventsCollection['events'])) {
            $hostedEvents = $hostedEventsCollection['events'];
        }

        $talkComments = $talkApi->getComments($user->getTalkCommentsUri(), true, 5);
        foreach ($talkComments as $comment) {
            if (isset($talkInfo[$comment->getTalkUri()])) {
                continue;
            }
            $talkData = $talkDb->load('uri', $comment->getTalkUri());
            if ($talkData) {
                $eventUri = $talkData['event_uri'];
                $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title']  = $talkData['slug'];
            } else {
                $talk = $talkApi->getTalk($comment->getTalkUri());
                if ($talk) {
                    $eventUri = $talk->getEventUri();
                    $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title'] = $talk->getUrlFriendlyTalkTitle();
                }
            }

            // look up event's name & url_friendly_name from the DB orAPI
            if (!isset($eventInfo[$comment->getTalkUri()])) {
                $eventInfo[$comment->getTalkUri()] = $this->lookupEventInfo($eventUri);
            }
        }

        echo $this->render(
            'User/profile.html.twig',
            array(
                'thisUser'         => $user,
                'talks'            => $talks,
                'eventInfo'        => $eventInfo,
                'talkInfo'         => $talkInfo,
                'events'           => $events,
                'hostedEvents'     => $hostedEvents,
                'talkComments'     => $talkComments,
            )
        );
    }

    /*
     * User profile talks detail page
     *
     * @param  string $username User's username
     * @return void
     */
    public function profileTalks($username)
    {
        $userApi = $this->getUserApi();
        $user = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $talkApi = $this->getTalkApi();

        $talkCollection = $talkApi->getCollection($user->getTalksUri(), ['verbose' => 'yes', 'resultsperpage' => 0]);
        if (!isset($talkCollection['talks'])) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }

        $eventInfo = array();
        if (isset($talkCollection['talks'])) {
            $talks = $talkCollection['talks'];
            foreach ($talks as $talk) {
                // look up event's name & url_friendly_name from the DB orAPI
                if (!isset($eventInfo[$talk->getApiUri()])) {
                    $eventInfo[$talk->getApiUri()] = $this->lookupEventInfo($talk->getEventUri());
                }
            }
        }

        echo $this->render(
            'User/profile-talks.html.twig',
            array(
                'thisUser'  => $user,
                'talks'     => $talks,
                'eventInfo' => $eventInfo,
            )
        );
    }

    /*
     * User profile attended events detail page
     *
     * @param  string $username User's username
     * @return void
     */
    public function profileEvents($username)
    {
        $userApi = $this->getUserApi();
        $user = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $eventApi = $this->getEventApi();
        $eventsCollection = $eventApi->getCollection(
            $user->getAttendedEventsUri(),
            ['verbose' => 'yes', 'resultsperpage' => 0]
        );
        if (!isset($eventsCollection['events'])) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }

        echo $this->render(
            'User/profile-events.html.twig',
            array(
                'thisUser' => $user,
                'events'   => $eventsCollection['events'],
                'type'     => 'attended',
            )
        );
    }

    /*
     * User profile hosted events detail page
     *
     * @param  string $username User's username
     * @return void
     */
    public function profileHosted($username)
    {
        $userApi = $this->getUserApi();
        $user = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $eventApi = $this->getEventApi();
        $hostedEventsCollection = $eventApi->getCollection(
            $user->getHostedEventsUri(),
            ['verbose' => 'yes', 'resultsperpage' => 5]
        );
        if (!isset($hostedEventsCollection['events'])) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }


        echo $this->render(
            'User/profile-events.html.twig',
            array(
                'thisUser' => $user,
                'events'   => $hostedEventsCollection['events'],
                'type'     => 'hosted',
            )
        );
    }

    /*
     * User profile comments detail page
     *
     * @param  string $username User's username
     * @return void
     */
    public function profileComments($username)
    {
        $userApi = $this->getUserApi();
        $user = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $talkDb = $this->getTalkDb();
        $talkApi = $this->getTalkApi();
        $eventApi = $this->getEventApi();
        $talkComments = $talkApi->getComments($user->getTalkCommentsUri(), true, 0);
        if (!$talkComments) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }

        $talkInfo = array();
        $eventInfo = array();
        foreach ($talkComments as $comment) {
            if (isset($talkInfo[$comment->getTalkUri()])) {
                continue;
            }
            $talkData = $talkDb->load('uri', $comment->getTalkUri());
            if ($talkData) {
                $eventUri = $talkData['event_uri'];
                $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title']  = $talkData['slug'];
            } else {
                $talk = $talkApi->getTalk($comment->getTalkUri());
                if ($talk) {
                    $eventUri = $talk->getEventUri();
                    $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title'] = $talk->getUrlFriendlyTalkTitle();
                }
            }

            // look up event's name & url_friendly_name from the DB orAPI
            if (!isset($eventInfo[$comment->getTalkUri()])) {
                $eventInfo[$comment->getTalkUri()] = $this->lookupEventInfo($eventUri);
            }
        }

        echo $this->render(
            'User/profile-comments.html.twig',
            array(
                'thisUser'     => $user,
                'talkComments' => $talkComments,
                'eventInfo'    => $eventInfo,
                'talkInfo'     => $talkInfo,
            )
        );
    }

    protected function lookupEventInfo($eventUri)
    {
        $eventDb = $this->getEventDb();
        $eventApi = $this->getEventApi();

        $eventInfo = array();
        $eventData = $eventDb->load('uri', $eventUri);
        if (isset($eventData['name'])) {
            $eventInfo['url_friendly_name'] = $eventData['url_friendly_name'];
            $eventInfo['name'] = $eventData['name'];
        } else {
            $event = $eventApi->getEvent($eventUri);
            if ($event) {
                $eventInfo['url_friendly_name'] = $event->getUrlFriendlyName();
                $eventInfo['name'] = $event->getName();
            }
        }

        return $eventInfo;
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
     * @return UserApi
     */
    private function getUserApi()
    {
        $cache = $this->getCache();
        return new UserApi($this->cfg, $this->accessToken, new UserDb($cache));
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
     * @return EventDb
     */
    private function getEventDb()
    {
        return new EventDb($this->getCache());
    }

    /**
     * @return EventApi
     */
    private function getEventApi()
    {
        return new EventApi($this->cfg, $this->accessToken, $this->getEventDb(), $this->getUserApi());
    }
}
