<?php
namespace User;

use Application\BaseApi;

class AuthApi extends BaseApi
{
    /**
     * Log in via the API
     *
     * @param  string $username username
     * @param  string $password password
     * @param  string $clientId OAuth client ID
     * @param  string $clientSecret OAuth client secret
     * @return mixed            stdClass of token and user's URI
     */
    public function login($username, $password, $clientId, $clientSecret)
    {
        $url = $this->baseApiUrl . '/v2.1/token';
        $params = array(
            'grant_type'    => 'password',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'username'      => $username,
            'password'      => $password,
        );

        list($status, $result) = $this->apiPost($url, $params);
        if ($result) {
            $data = json_decode($result);
            if ($data) {
                if (isset($data->access_token)) {
                    return $data;
                }
            }
        }
        return false;
    }
}
