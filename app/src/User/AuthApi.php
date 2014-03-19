<?php
namespace Joindin\Model\API;

class Auth extends \Joindin\Model\API\JoindIn
{
    /**
     * Log in via the API
     *
     * @param  string $username username
     * @param  string $password password
     * @return mixed            stdClass of token and user's URI
     */
    public function login($username, $password, $clientId)
    {
        $url = $this->baseApiUrl . '/v2.1/token';
        $params = array(
            'grant_type' => 'password',
            'client_id'  => $clientId,
            'username'   => $username,
            'password'   => $password,
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
