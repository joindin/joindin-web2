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
        $app->map('/user/:username', array($this, 'profile'))->via('GET', 'POST')->name('user-profile');
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
                $error = true;
            } else {
                session_regenerate_id(true);
                $_SESSION['access_token'] = $result->access_token;
                $this->accessToken = $_SESSION['access_token'];

                // now get users details
                $keyPrefix = $this->cfg['redisKeyPrefix'];
                $cache = new CacheService($keyPrefix);
                $userApi = new UserApi($this->cfg, $this->accessToken, new UserDb($cache));
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

        $this->render('User/login.html.twig', array('error' => $error));
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
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $userApi = new UserApi($this->cfg, $this->accessToken, new UserDb($cache));

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
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $userApi = new UserApi($this->cfg, $this->accessToken, new UserDb($cache));

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

                $keyPrefix = $this->cfg['redisKeyPrefix'];
                $cache = new CacheService($keyPrefix);
                $userApi = new UserApi($this->cfg, $this->accessToken, new UserDb($cache));

                $result = false;
                try {
                    $result = $userApi->reverify($email);
                    if ($result) {
                        $this->application->flash('message', 'We have resent your welcome email. Please check your email to verify your account before logging in.');
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
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $userDb = new UserDb($cache);
        $userApi = new UserApi($this->cfg, $this->accessToken, $userDb);

        $userUri = $userDb->load('username', $username);
        if ($userUri) {
            $user = $userApi->getUser($userUri);
        } else {
            $user = $userApi->getUserByUsername($username);
            if (!$user) {
                Slim::getInstance()->notFound();
            }
            $userDb->save($user);
        }

        $talkDb = new TalkDb($cache);
        $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb);
        $eventDb = new EventDb($cache);
        $eventApi = new EventApi($this->cfg, $this->accessToken, $eventDb);

        $talkCollection = $talkApi->getCollection($user->getTalksUri().'?verbose=yes');
        $talks = false;
        $talkEvents = array();
        $eventStubNames = array();
        if (isset($talkCollection['talks'])) {
            $talks = $talkCollection['talks'];
            // need the full web2 url for each talk - we can get this via the db
            foreach ($talks as $talk) {
                $event = $eventApi->getEvent($talk->getEventUri());
                if ($event) {
                    $eventDb->save($event);
                    $talkEvents[$talk->getApiUri()] = $event;
                    $talkDb->save($talk, $event->getUri());
                }
            }
        }

        $eventsCollection = $eventApi->queryEvents($user->getAttendedEventsUri());
        $events = false;
        if (isset($eventsCollection['events'])) {
            $events = $eventsCollection['events'];
        }

        $hostedEventsCollection = $eventApi->queryEvents($user->getHostedEventsUri());
        $hostedEvents = false;
        if (isset($hostedEventsCollection['events'])) {
            $hostedEvents = $hostedEventsCollection['events'];
        }

        $talkComments = $talkApi->getComments($user->getTalkCommentsUri(), true);
        $urlFriendlyTalkTitles = array(); // look up a url-friendly-talk-title from a talk-uri
        foreach ($talkComments as $comment) {
            $talk = $talkApi->getTalk($comment->getTalkUri());
            if ($talk) {
                $urlFriendlyTalkTitles[$comment->getTalkUri()] = $talk->getUrlFriendlyTalkTitle();
                $talkDb->save($talk, $talk->getEventUri());
                $event = $eventApi->getEvent($talk->getEventUri());
                if ($event) {
                    $eventDb->save($event);
                    $talkEvents[$comment->getTalkUri()] = $event;
                }
            }
        }

        // summary page  - only need 5 items
        $moreTalks = false;
        if ($talks) {
            $moreTalks = count($talks) > 5 ? true : false;
            $talks = array_splice($talks, 0, 5);
        }

        $moreEvents = false;
        if ($events) {
            $moreEvents = count($events) > 5 ? true : false;
            $events = array_splice($events, 0, 5);
        }
        
        $moreHostedEvents = false;
        if ($hostedEvents) {
            $moreHostedEvents = count($hostedEvents) > 5 ? true : false;
            $hostedEvents = array_splice($hostedEvents, 0, 5);
        }

        $moreTalkComments = false;
        if ($talkComments) {
            $moreTalkComments = count($talkComments) > 5 ? true : false;
            $talkComments = array_splice($talkComments, 0, 5);
        }

        echo $this->render(
            'User/profile.html.twig',
            array(
                'thisUser'              => $user,
                'talks'                 => $talks,
                'talkEvents'            => $talkEvents,
                'urlFriendlyTalkTitles' => $urlFriendlyTalkTitles,
                'moreTalks'             => $moreTalks,
                'events'                => $events,
                'moreEvents'            => $moreEvents,
                'hostedEvents'          => $hostedEvents,
                'moreHostedEvents'      => $moreHostedEvents,
                'talkComments'          => $talkComments,
                'moreTalkComments'      => $moreTalkComments,
            )
        );
    }
}
