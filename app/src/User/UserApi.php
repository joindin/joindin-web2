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
     * Change the user's password
     *
     * @param  string $password The new password
     *
     * @return mixed the access token or false  
     */
    public function setPassword($clientId, $clientSecret, $password)
    {
        $url = $this->baseApiUrl . '/v2.1/password';
        $params = array(
            'grant_type'    => 'password',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'password'      => $password,
        );

        list($status, $result) = $this->apiPost($url, $params);
        switch ($status) {
            case 200:
                if ($result) {
                     $data = json_decode($result);
                     if ($data) {
                         if (isset($data->access_token)) {
                             return $data;
                         }
                     }
                }
                break;
            default:
                break;
        }

        return false;
    }
}

