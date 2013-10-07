<?php
namespace Joindin\Controller;

use Joindin\Model\API\Auth as AuthService;
use Joindin\Model\API\User as UserService;

class User extends Base
{

    /**
     * Login page
     *
     */
    public function login()
    {
        $request = $this->application->request();

        $error = false;
        if ($request->isPost()) {
            // handle submission of login form
        
            // make a call to the api with granttype=password
            
            $username = $request->post('username');
            $password = $request->post('password');

            $authService = new AuthService();
            $result      = $authService->login($username, $password);

            if (false === $result) {
                $error = true;
            } else {
                session_regenerate_id();
                $_SESSION['access_token'] = $result->access_token;

                // now get users details
                $userService = new UserService();
                $user = $userService->getUser($result->user_uri);
                if ($user) {
                    $_SESSION['user'] = $user;
                    $this->application->redirect('/');
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
     * Register page
     *
     */
    public function register()
    {
        // TODO: Implement!
        // This empty method exists so that the named route can be defined
        // for the login page text.
    }


    /**
     * Log out
     *
     */
    public function logout()
    {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        if (isset($_SESSION['access_token'])) {
            unset($_SESSION['access_token']);
        }
        session_regenerate_id();
        $this->application->redirect('/');
    }

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/user/logout', array($this, 'logout'))->name('user-logout');
        $app->map('/user/login', array($this, 'login'))->via('GET', 'POST')->name('user-login');
        $app->map('/user/register', array($this, 'register'))->via('GET', 'POST')->name('user-register');
    }
}



