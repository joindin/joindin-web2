<?php
/*
 * PROBLEMS LOGGING IN?
 *
 * If you want to use User related features, you will need to use a locally
 * checked out copy of the API: you cannot log into the live API
 */

namespace Joindin\Controller;

use Joindin\Model\API\Auth as AuthService;
use Joindin\Model\API\User as UserService;
use \Joindin\Service\Helper\Config as Config;

class User extends Base
{
    /**
     * Routes implemented by this class
     *
     * @param  Slim   $app Slim application instance
     *
     * @return void
     */
    protected function defineRoutes(\Slim $app)
    {
        $app->get('/user/logout', array($this, 'logout'))->name('user-logout');
        $app->map('/user/login', array($this, 'login'))->via('GET', 'POST')->name('user-login');
        $app->map('/user/register', array($this, 'register'))->via('GET', 'POST')->name('user-register');
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
            $clientId = $config['client_id'];

            $authService = new AuthService(new Config(), $this->accessToken);
            $result      = $authService->login($username, $password, $clientId);

            if (false === $result) {
                $error = true;
            } else {
                session_regenerate_id(true);
                $_SESSION['access_token'] = $result->access_token;
                $this->accessToken = $_SESSION['access_token'];

                // now get users details
                $userService = new UserService(new Config(), $this->accessToken);
                $user = $userService->getUser($result->user_uri);
                if ($user) {
                    $_SESSION['user'] = $user;
                    $this->application->redirect('/');
                } else {
                    unset($_SESSION['access_token']);
                }
            }
        }

        echo $this->application->render(
            'User/login.html.twig',
            array(
                'error' => $error,
            )
        );
    }

    /**
     * Registration page
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement!
        // This  method exists so that the named route can be defined
        // for the login page text.
        
        // For now, we just redirect to the legacy site
        header("Location: https://joind.in/user/register");
        exit;
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
}
