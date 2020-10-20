<?php
namespace User;

use Application\BaseController;
use Event\EventApi;
use Event\EventDb;
use Slim\Slim;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Talk\TalkApi;
use Talk\TalkDb;

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
        $app->get('/user/logout', [$this, 'logout'])->name('user-logout');
        $app->map('/user/login', [$this, 'login'])->via('GET', 'POST')->name('user-login');
        $app->map('/user/register', [$this, 'register'])->via('GET', 'POST')->name('user-register');
        $app->get('/user/verification', [$this, 'verification'])->name('user-verification');
        $app->map('/user/resend-verification', [$this, 'resendVerification'])
            ->via('GET', 'POST')->name('user-resend-verification');
        $app->map('/user/username-reminder', [$this, 'remindUsername'])
            ->via('GET', 'POST')->name('user-username-reminder');
        $app->map('/user/password-reset', [$this, 'resetPassword'])
            ->via('GET', 'POST')->name('user-password-reset');
        $app->map('/user/new-password', [$this, 'newPassword'])
            ->via('GET', 'POST')->name('user-new-password');
        $app->get('/user/twitter-login', [$this, 'loginWithTwitter'])->name('twitter-login');
        $app->get('/user/twitter-access', [$this, 'accessTokenFromTwitter'])->name('twitter-callback');
        $app->get('/user/facebook-access', [$this, 'accessTokenFromFacebook'])->name('facebook-callback');
        $app->get('/user/:username', [$this, 'profile'])->name('user-profile');
        $app->get('/user/:username/talks', [$this, 'profileTalks'])->name('user-profile-talks');
        $app->get('/user/:username/events', [$this, 'profileEvents'])->name('user-profile-events');
        $app->get('/user/:username/hosted', [$this, 'profileHosted'])->name('user-profile-hosted');
        $app->get('/user/:username/comments', [$this, 'profileComments'])->name('user-profile-comments');
        $app->map('/user/:username/edit', [$this, 'profileEdit'])
            ->via('GET', 'POST')->name('user-profile-edit');
        $app->get('/user/:username/delete', [$this, 'userDelete'])->name('user-profile-delete');
        $app->get('/user/view/:userId(/:extra+)', [$this, 'redirectFromId'])
            ->name('user-redirect-from-id')
            ->conditions(['userId' => '\d+']);
    }

    /**
     * Login page
     *
     * @return void
     */
    public function login()
    {
        $config  = $this->application->config('oauth');
        $request = $this->application->request();

        $error = false;
        if ($request->isPost()) {
            // handle submission of login form

            // make a call to the api with granttype=password
            $username     = $request->post('username');
            $password     = $request->post('password');
            $redirect     = $request->post('redirect');
            $clientId     = $config['client_id'];
            $clientSecret = $config['client_secret'];

            $authApi = $this->application->container->get(AuthApi::class);
            $result  = $authApi->login($username, $password, $clientId, $clientSecret);

            $this->handleLogin($result, $redirect);
        }

        $this->application->redirect('/');
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
        $form    = $factory->create(RegisterFormType::class);

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
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Submits the form data to the API and returns info for use by register()!
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param FormInterface $form
     *
     * @return mixed
     */
    protected function registerUserUsingForm($form)
    {
        $values  = $form->getData();
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
        $request  = $this->application->request();
        $redirect = ($request->get('redirect')) ? $request->get('redirect') : "/";

        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        if (isset($_SESSION['access_token'])) {
            unset($_SESSION['access_token']);
        }
        session_regenerate_id(true);
        $this->application->redirect($redirect);
    }

    /**
     * Accept a user's email verification
     *
     * @return void
     */
    public function verification()
    {
        $request = $this->application->request();

        $token   = $request->get('token');
        $userApi = $this->getUserApi();

        try {
            $userApi->verify($token);
            $this->application->flash('message', "Thank you for verifying your email address. You can now log in.");
        } catch (\Exception $e) {
            $this->application->flash('error', "Sorry, your verification link was invalid.");
        }

        $this->application->redirect('/');
    }

    public function resendVerification()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(EmailInputFormType::class);

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getData();
                $email  = $values['email'];

                $userApi = $this->getUserApi();

                try {
                    $result = $userApi->reverify($email);
                    if ($result) {
                        $this->application->flash(
                            'message',
                            'We have resent your welcome email. Please check ' .
                            'your email to verify your account before logging in.'
                        );
                        $this->application->redirect('/');
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
            [
                'form' => $form->createView(),
            ]
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
        $user    = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $talkDb   = $this->getTalkDb();
        $talkApi  = $this->getTalkApi();
        $eventApi = $this->getEventApi();
        $eventUri = null;

        $eventInfo = []; // look up an event's name and url_friendly_name from its uri
        $talkInfo  = []; // look up a talk's url_friendly_talk_title from its uri

        $talkCollection = $talkApi->getCollection($user->getTalksUri(), ['verbose' => 'yes', 'resultsperpage' => 5]);
        $talks          = false;
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
                $eventUri                                                     = $talkData['event_uri'];
                $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title']  = $talkData['slug'];
            } else {
                $talk = $talkApi->getTalk($comment->getTalkUri());
                if ($talk) {
                    $eventUri                                                    = $talk->getEventUri();
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
            [
                'thisUser'         => $user,
                'talks'            => $talks,
                'eventInfo'        => $eventInfo,
                'talkInfo'         => $talkInfo,
                'events'           => $events,
                'hostedEvents'     => $hostedEvents,
                'talkComments'     => $talkComments,
            ]
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
        $user    = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $talkApi = $this->getTalkApi();

        $talkCollection = $talkApi->getCollection($user->getTalksUri(), ['verbose' => 'yes', 'resultsperpage' => 0]);
        if (!isset($talkCollection['talks'])) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }

        $eventInfo = [];
        $talks     = [];
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
            [
                'thisUser'  => $user,
                'talks'     => $talks,
                'eventInfo' => $eventInfo,
            ]
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
        $user    = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $eventApi         = $this->getEventApi();
        $eventsCollection = $eventApi->getCollection(
            $user->getAttendedEventsUri(),
            ['verbose' => 'yes', 'resultsperpage' => 0]
        );
        if (!isset($eventsCollection['events'])) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }

        echo $this->render(
            'User/profile-events.html.twig',
            [
                'thisUser' => $user,
                'events'   => $eventsCollection['events'],
                'type'     => 'attended',
            ]
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
        $user    = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $eventApi               = $this->getEventApi();
        $hostedEventsCollection = $eventApi->getCollection(
            $user->getHostedEventsUri(),
            ['verbose' => 'yes', 'resultsperpage' => 0]
        );
        if (!isset($hostedEventsCollection['events'])) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }


        echo $this->render(
            'User/profile-events.html.twig',
            [
                'thisUser' => $user,
                'events'   => $hostedEventsCollection['events'],
                'type'     => 'hosted',
            ]
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
        $user    = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        $talkDb       = $this->getTalkDb();
        $talkApi      = $this->getTalkApi();
        $eventApi     = $this->getEventApi();
        $eventUri     = null;
        $talkComments = $talkApi->getComments($user->getTalkCommentsUri(), true, 0);
        if (!$talkComments) {
            $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
        }

        $talkInfo  = [];
        $eventInfo = [];
        foreach ($talkComments as $comment) {
            if (isset($talkInfo[$comment->getTalkUri()])) {
                continue;
            }
            $talkData = $talkDb->load('uri', $comment->getTalkUri());
            if ($talkData) {
                $eventUri                                                     = $talkData['event_uri'];
                $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title']  = $talkData['slug'];
            } else {
                $talk = $talkApi->getTalk($comment->getTalkUri());
                if ($talk) {
                    $eventUri                                                    = $talk->getEventUri();
                    $talkInfo[$comment->getTalkUri()]['url_friendly_talk_title'] = $talk->getUrlFriendlyTalkTitle();
                }
            }

            // look up event's name & url_friendly_name from the DB orAPI
            if (!isset($eventInfo[$comment->getTalkUri()])) {
                $eventInfo[$comment->getTalkUri()] = $this->lookupEventInfo($eventUri);
            }
        }

        $this->render(
            'User/profile-comments.html.twig',
            [
                'thisUser'     => $user,
                'talkComments' => $talkComments,
                'eventInfo'    => $eventInfo,
                'talkInfo'     => $talkInfo,
            ]
        );
    }

    protected function lookupEventInfo($eventUri)
    {
        $eventDb  = $this->getEventDb();
        $eventApi = $this->getEventApi();

        $eventInfo = [];
        $eventData = $eventDb->load('uri', $eventUri);
        if (isset($eventData['name'])) {
            $eventInfo['url_friendly_name'] = $eventData['url_friendly_name'];
            $eventInfo['name']              = $eventData['name'];
        } else {
            $event = $eventApi->getEvent($eventUri);
            if ($event) {
                $eventInfo['url_friendly_name'] = $event->getUrlFriendlyName();
                $eventInfo['name']              = $event->getName();
            }
        }

        return $eventInfo;
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        return $this->application->container->get(UserApi::class);
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
     * @return EventDb
     */
    private function getEventDb()
    {
        return $this->application->container->get(EventDb::class);
    }

    /**
     * @return EventApi
     */
    private function getEventApi()
    {
        return $this->application->container->get(EventApi::class);
    }

    public function remindUsername()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(EmailInputFormType::class);

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getData();
                $email  = $values['email'];

                $userApi = $this->getUserApi();

                try {
                    $result = $userApi->usernameReminder($email);
                    if ($result) {
                        $this->application->flash(
                            'message',
                            'Check your email to find a reminder of your username.'
                        );
                        $this->application->redirect('/');
                    }
                } catch (\Exception $e) {
                    $form->addError(
                        new FormError('An error occurred: ' . $e->getMessage())
                    );
                }
            }
        }

        $this->render(
            'User/username-reminder.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * User profile edit page
     *
     * @param  string $username User's username
     * @return void
     */
    public function profileEdit($username)
    {
        $userApi = $this->getUserApi();
        $user    = $userApi->getUserByUsername($username);
        if (!$user) {
            Slim::getInstance()->notFound();
        }

        if (!$user->getCanEdit() || !isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $this->application
                    ->urlFor('user-profile-edit', ['username' => $username])
            );
        }

        // create an array of the data to be edited for use by the form
        $userData = [
            'full_name'        => $user->getFullName(),
            'email'            => $user->getEmail(),
            'twitter_username' => $user->getTwitterUsername(),
            'biography'        => $user->getBiography(),
            'old_password'     => '',
            'password'         => '',
        ];

        // can only change password if we're editing ourselves
        $canChangePassword = ($user->getUri() == $_SESSION['user']->getUri());

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(UserFormType::class, $userData, ['can_change_password' => true]);

        $request = $this->application->request();
        if ($request->isPost()) {
            if ($request->post('submit') == 'Cancel') {
                $this->application->redirect($this->application->urlFor('user-profile', ['username' => $username]));
            }

            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                try {
                    $values = $form->getData();
                    // the form will convert twitter_username to NULL if it's empty, so we need to put it back
                    $values['twitter_username'] = (string)$values['twitter_username'];

                    $userApi = $this->getUserApi();
                    // LDBG($values);exit;
                    $result = $userApi->edit($user->getUri(), $values);
                    if ($result instanceof UserEntity) {
                        if ($_SESSION['user']->getUri() == $result->getUri()) {
                            // update the session
                            $_SESSION['user'] = $result;
                        }

                        $this->application->flash('message', 'Profile has been updated');
                        $this->application->redirect(
                            $this->application->urlFor('user-profile', ['username' => $username])
                        );
                    }
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    if (strpos($message, 'The credentials could not be verified')) {
                        $form->get('old_password')->addError(
                            new FormError('Your current password is incorrect')
                        );
                    } else {
                        $form->addError(
                            new FormError('An error occurred: ' . $message)
                        );
                    }
                }
            }
        }

        $this->render(
            'User/profile-edit.html.twig',
            [
                'thisUser'            => $user,
                'form'                => $form->createView(),
                'can_change_password' => $canChangePassword,
            ]
        );
    }

    public function userDelete($username)
    {
        $userApi = $this->getUserApi();
        $user    = $userApi->getUserByUsername($username);

        try {
            // LDBG($values);exit;
            $result = $userApi->delete($user->getUri());

            $this->application->flash('message', 'User has been deleted');
        } catch (\Exception $e) {
            $this->application->flash('error', 'There was a problem deleting the user: ' . $e->getMessage());
            $this->application->redirect(
                $this->application->urlFor('user-profile-edit', ['username' => $username])
            );
        }

        $this->application->redirect('/');
    }

    public function resetPassword()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(UsernameInputFormType::class);

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values   = $form->getData();
                $username = $values['username'];

                $userApi = $this->getUserApi();

                try {
                    $result = $userApi->passwordReset($username);
                    if ($result) {
                        $this->application->flash(
                            'message',
                            'Check your email for instructions on resetting your password.'
                        );
                        $this->application->redirect('/');
                    }
                } catch (\Exception $e) {
                    $form->addError(
                        new FormError('An error occurred: ' . $e->getMessage())
                    );
                }
            }
        }

        $this->render(
            'User/password-reset.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Link in password reset email lands here
     *
     * @return void
     */
    public function newPassword()
    {
        $request = $this->application->request();
        $token   = $request->get('token');

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(NewPasswordFormType::class);

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values  = $form->getData();
                $userApi = $this->getUserApi();

                try {
                    $result = $userApi->resetPassword($token, $values['password']);
                    if ($result) {
                        $this->application->flash(
                            'message',
                            'Your password was saved; you may now log in.'
                        );
                        $this->application->redirect('/');
                    }
                } catch (\Exception $e) {
                    $form->addError(
                        new FormError('An error occurred: ' . $e->getMessage())
                    );
                }
            }
        }

        $this->render(
            'User/new-password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * This gets a request token via the API, and forwards the user
     * to Twitter to log in and grant us access
     */
    public function loginWithTwitter()
    {
        // ask the API for a request token
        $config       = $this->application->config('oauth');
        $clientId     = $config['client_id'];
        $clientSecret = $config['client_secret'];

        $authApi       = $this->application->container->get(AuthApi::class);
        $request_token = $authApi->getTwitterRequestToken($clientId, $clientSecret);

        if ($request_token) {
            // forward the user
            header("Location: https://api.twitter.com/oauth/authenticate?oauth_token=" . $request_token);
            exit;
        }

        $this->application->flash(
            'error',
            'We could not log you in with twitter'
        );
        $this->application->redirect('/');
    }

    /**
     * The callback URL should point to here
     */
    public function accessTokenFromTwitter()
    {
        $config  = $this->application->config('oauth');
        $request = $this->application->request();

        // pass verification to the API so we can log in
        $clientId     = $config['client_id'];
        $clientSecret = $config['client_secret'];

        // handle incoming vars
        $token    = $request->get('oauth_token');
        $verifier = $request->get('oauth_verifier');

        $authApi = $this->application->container->get(AuthApi::class);
        $result  = $authApi->verifyTwitter($clientId, $clientSecret, $token, $verifier);

        $this->handleLogin($result);
    }

    /**
     * The Facebook callback URL returns here
     */
    public function accessTokenFromFacebook()
    {
        $config  = $this->application->config('oauth');
        $request = $this->application->request();

        // pass verification to the API so we can log in
        $clientId     = $config['client_id'];
        $clientSecret = $config['client_secret'];

        // handle incoming vars
        $code = $request->get('code');

        $authApi = $this->application->container->get(AuthApi::class);
        $result  = $authApi->verifyFacebook($clientId, $clientSecret, $code);

        $this->handleLogin($result);
    }

    /**
     * Process a user login result. If result is false, then we failed, otherwise
     * update the session.
     *
     * @param  \stdClass|false  $result
     * @param  string           $redirect
     * @return void
     */
    protected function handleLogin($result, $redirect = '')
    {
        if (!is_object($result)) {
            if ($result === false || $result[0] == 'Signin failed') {
                $this->application->flash('error', "Failed to log in");
            }
            if ($result[0] == 'Not verified') {
                $message = 'User account not verified. ' .
                    "<a href='/user/resend-verification'>Click here</a> to resend welcome email.";
                $this->application
                    ->flash('error', $message);
            }

            if (empty($redirect)) {
                $redirect = '/';
            }
            $this->application->redirect($redirect . '#login');
        }

        session_regenerate_id(true);
        $_SESSION['access_token'] = $result->access_token;
        $this->accessToken        = $_SESSION['access_token'];

        // now get users details
        $userApi = $this->getUserApi();

        $userApi->setAccessToken($this->accessToken);

        $user = $userApi->getUser($result->user_uri);
        if ($user) {
            $_SESSION['user'] = $user;
            if (empty($redirect) || strpos($redirect, '/user/login') === 0) {
                $this->application->redirect('/');
            }
            $this->application->redirect($redirect);
        }
        unset($_SESSION['access_token']);
        $this->application->flash('error', "Failed to log in. User account problem.");
        $this->application->redirect('/');
    }

    public function redirectFromId($userId)
    {
        $userApi = $this->getUserApi();
        $user    = $userApi->getUserByUserId($userId);
        if (!$user) {
            return $this->application->notFound();
        }

        $this->application->redirect(
            $this->application->urlFor(
                'user-profile',
                ['username' => $user->getUsername()]
            )
        );
    }
}
