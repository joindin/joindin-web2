<?php
namespace User;

use Application\BaseApi;

class UserApi extends BaseApi
{
    private \User\UserDb $userDb;

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
    public function getUser(string $url)
    {
        $result = $this->apiGet($url, ['verbose'=>'yes']);

        if ($result) {
            $data = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
            if (isset($data->users[0])) {
                $userEntity = new UserEntity($data->users[0]);
                $this->userDb->save($userEntity);
                return $userEntity;
            }
        }
        return false;
    }

    /**
     * @param integer $userId
     * @return UserEntity|null
     */
    public function getUserByUserId($userId)
    {
        $userId = (int)$userId;
        if ($userId === 0) {
            return null;
        }

        $userUrl = $this->baseApiUrl . '/v2.1/users/' . $userId;

        return $this->getUser($userUrl);
    }

    /**
     * Takes the fields from the registration form, and passes them though
     * to the API to register a new user
     *
     * @param array $data   The fields from the registration form
     *
     * @see RegisterFormType::buildForm() for a list of supported fields in the $data array and their constraints.
     *
     * @throws \Exception if a status code other than 201 is returned.
     *
     * @return bool         True if the user is created
     */
    public function register($data): bool
    {
        [$status, $result, $headers] = $this->apiPost($this->baseApiUrl . '/v2.1/users', $data);

        if ($status == 201) {
            // user URI in $headers['location'] but the user is pending so it's not useful
            return true;
        }

        $message = json_decode($result);
        $message = is_array($message) ? current($message) : 'User could not be saved';

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
    public function verify($token): bool
    {
        $data = ["token" => $token];

        [$status, $result, $headers] = $this->apiPost($this->baseApiUrl . '/v2.1/users/verifications', $data);

        if ($status == 204) {
            return true;
        }

        throw new \Exception('Verification failed');
    }

    /**
     * Get the backend to send a new verification token to this email
     *
     * @param string $email  The email address of the user who needs a new token
     *
     * @throws \Exception   If an error occurs (not a 202 response)
     *
     * return bool  True if successful
     */
    public function reverify($email): bool
    {
        $data = ["email" => $email];

        [$status, $result, $headers] = $this->apiPost($this->baseApiUrl . '/v2.1/emails/verifications', $data);

        if ($status == 202) {
            return true;
        }

        $message = json_decode($result);
        $message = is_array($message) ? current($message) : "Unknown error";
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
        // fetch via filtering the users collection
        $url    = $this->baseApiUrl . '/v2.1/users';
        $result = $this->apiGet($url, ['username' => $username, 'verbose'=>'yes']);

        if ($result) {
            $data = json_decode($result);
            if ($data && isset($data->users)) {
                foreach ($data->users as $userData) {
                    if (strtolower($userData->username) === strtolower($username)) {
                        $userEntity = new UserEntity($userData);
                        $this->userDb->save($userEntity);
                        return $userEntity;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get a user's username
     *
     * @param  string $uri
     * @return string|null
     */
    public function getUsername($uri)
    {
        // do we already know it?
        $userInfo = $this->userDb->load('uri', $uri);
        if ($userInfo) {
            return $userInfo['username'];
        }

        // get from API
        $user = $this->getUser($uri);
        if ($user) {
            return $user->getUsername();
        }

        return null;
    }

    /**
     * Ask the API to email the user to remind them of their username
     *
     * @param string $email  The email address of the user to remind
     *
     * @throws \Exception   If an error occurs (not a 202 response)
     *
     * return bool  True if successful
     */
    public function usernameReminder($email): bool
    {
        $data = ["email" => $email];

        [$status, $result, $headers] = $this->apiPost(
            $this->baseApiUrl . '/v2.1/emails/reminders/username',
            $data
        );

        if ($status == 202) {
            return true;
        }

        $message = json_decode($result);
        $message = is_array($message) ? current($message) : "Unknown error";
        throw new \Exception($message);
    }

    /**
     * Ask the API to email the user a token to reset their password
     *
     * @param string $username  The username address of the user to remind
     *
     * @throws \Exception   If an error occurs (not a 202 response)
     *
     * @return bool  True if successful
     */
    public function passwordReset($username): bool
    {
        $data = ["username" => $username];

        [$status, $result, $headers] = $this->apiPost(
            $this->baseApiUrl . '/v2.1/emails/reminders/password',
            $data
        );

        if ($status == 202) {
            return true;
        }

        $message = json_decode($result);
        $message = is_array($message) ? current($message) : "Unknown error";
        throw new \Exception($message);
    }

    /**
     * Update a user's details
     *
     * @see http://joindin.github.io/joindin-api/users.html for a list of supported
     * fields in the $data array
     *
     *
     * @throws \Exception if unsuccessful
     * @return UserEntity
     */
    public function edit($uri, array $data)
    {
        [$status, $result, $headers] = $this->apiPut($uri, $data);

        // if successful, return event entity represented by the URL in the Location header
        if ($status == 204) {
            // Retrieve a new copy of the user
            return $this->getUser($uri);
        }

        throw new \Exception('Your profile update was not accepted. The server reports: ' . $result);
    }


    public function delete(string $uri): bool
    {
        [$status, $result] = $this->apiDelete($uri, []);

        if ($status == 204) {
            return true;
        }

        throw new \Exception("Unable to delete user: $status, $result");
    }

    /**
     * Set a new password for a user who has forgotten theirs
     *
     * @param  string $token    The reset token we sent by email
     *
     * @throws \Exception       if a status code other than 201 is returned
     *
     * @return bool             True if the password was changed
     */
    public function resetPassword($token, $password): bool
    {
        $data = [
            "token"    => $token,
            "password" => $password,
        ];

        [$status, $result, $headers] = $this->apiPost($this->baseApiUrl . '/v2.1/users/passwords', $data);

        if ($status == 204) {
            return true;
        }

        throw new \Exception('The password could not be updated');
    }

    /**
     * Get all users for the specified query params
     *
     *
     * @throws \Exception   if unable to connect to API
     *
     */
    public function getCollection(array $queryParams = []): array
    {
        $usersUri = $this->baseApiUrl . '/v2.1/users';
        $users    = (array)json_decode(
            $this->apiGet($usersUri, $queryParams)
        );
        $meta = array_pop($users);

        $collectionData = [];
        foreach ($users['users'] as $item) {
            $user = new UserEntity($item);

            $collectionData['users'][] = $user;
            $this->userDb->save($user);
        }

        $collectionData['pagination'] = $meta;

        return $collectionData;
    }
}
