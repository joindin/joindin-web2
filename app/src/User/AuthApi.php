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

    /**
     * Get a request token from the API from Twitter
     *
     * @param  string $clientId OAuth client ID
     * @param  string $clientSecret OAuth client secret
     * @return string The token
     */
    public function getTwitterRequestToken($clientId, $clientSecret) {
        $url = $this->baseApiUrl . '/v2.1/twitter/request_token';
        $params = array(
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        );

        list ($status, $result, $headers) = $this->apiPost($url, $params);
        if ($status == 201) {
            // we got one, data is actually in the body
            $data = json_decode($result, true);
            if ($data) {
                return $data['token'];
            }
        }
        return false;
    }

    /**
     * Send Twitter verification token to the API to log us in
     *
     * @param  string $clientId OAuth client ID
     * @param  string $clientSecret OAuth client secret
     */
    public function verifyTwitter($clientId, $clientSecret, $token, $verifier) {
        $url = $this->baseApiUrl . '/v2.1/twitter/token';
        $params = array(
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'token'         => $token,
            'verifier'      => $verifier,
        );

        list ($status, $result, $headers) = $this->apiPost($url, $params);
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
