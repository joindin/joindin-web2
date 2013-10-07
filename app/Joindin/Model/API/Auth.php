<?php
namespace Joindin\Model\API;

class Auth extends \Joindin\Model\API\JoindIn
{
    public function login($username, $password)
    {
        $url = $this->baseApiUrl . '/v2.1/token';
        $params = array(
            'grant_type' => 'password',
            'client_id'  => 'web2',
            'username'   => $username,
            'password'   => $password,
        );

        $result = $this->apiPost($url, $params);

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
