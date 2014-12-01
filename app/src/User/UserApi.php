<?php
namespace User;

use Application\BaseApi;

class UserApi extends BaseApi
{

    public function __construct($config, $accessToken, UserDb $userDb)
    {
        parent::__construct($config, $accessToken);
        $this->userDb = $userDb;
    }

    /**
     * Retrieve user information from the API
     *
     * @param  string $url User's URI
     * @return mixed       stdClass of user data or false
     */
    public function getUser($url)
    {
        $result = $this->apiGet($url, array('verbose'=>'yes'));

        if ($result) {
            $data = json_decode($result);
            if ($data) {
                if (isset($data->users) && isset($data->users[0])) {
                    $user = new UserEntity($data->users[0]);

                    return $user;
                }

            }
        }
        return false;
    }

    /**
     * Takes the fields from the registration form, and passes them though
     * to the API to register a new user
     *
     * @param  Form $data   The fields from the registration form
     *
     * @see RegisterFormType::buildForm() for a list of supported fields in the $data array and their constraints.
     *
     * @throws \Exception if a status code other than 201 is returned.
     *
     * @return bool         True if the user is created
     */
    public function register($data)
    {

        list ($status, $result, $headers) = $this->apiPost($this->baseApiUrl . '/v2.1/users', $data);

        if ($status == 201) {
            // user URI in $headers['location'] but the user is pending so it's not useful
            return true;
        }

        $message = json_decode($result);
        if (is_array($message)) {
            $message = current($message);
        } else {
            $message = 'User could not be saved';
        }

        throw new \Exception($message);
    }

    /**
     * Send the offered verification token to the API
     *
     * @param  string $token    The verification token we sent by email
     *
     * @throws \Exception       if a status code other than 201 is returned.
     *
     * @return bool             True if the user is now verified
     */
    public function verify($token)
    {
        $data = array("token" => $token);

        list ($status, $result, $headers) = $this->apiPost($this->baseApiUrl . '/v2.1/users/verifications', $data);

        if ($status == 204) {
            return true;
        }

        throw new \Exception('Verification failed');
    }

    /**
     * Get the backend to send a new verification token to this email
     *
     * @param email $email  The email address of the user who needs a new token
     *
     * @throws \Exception   If an error occurs (not a 202 response)
     *
     * return bool  True if successful
     */
    public function reverify($email)
    {
        $data = array("email" => $email);

        list ($status, $result, $headers) = $this->apiPost($this->baseApiUrl . '/v2.1/emails/verifications', $data);

        if ($status == 202) {
            return true;
        }

        $message = json_decode($result);
        if (is_array($message)) {
            $message = current($message);
        } else {
            $message = "Unknown error";
        }
        throw new \Exception($message);
    }

    /**
     * Retrieve a user
     *
     * @param  string $username User's username
     * @return mixed            stdClass of user data or false
     */
    public function getUserByUsername($username)
    {
        $url = $this->baseApiUrl . '/v2.1/users';
        $result = $this->apiGet($url, ['username' => $username, 'verbose'=>'yes']);

        if ($result) {
            $data = json_decode($result);
            if ($data) {
                if (isset($data->users)) {
                    foreach ($data->users as $userData) {
                        if (strtolower($userData->username) == strtolower($username)) {
                            $user = new UserEntity($userData);
                            return $user;
                        }
                    }
                }
            }
        }
        return false;
    }
}
