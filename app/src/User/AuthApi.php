<?php
namespace User;

use Application\BaseApi;

class AuthApi extends BaseApi
{
    /**
     * Log in via the API
     *
     * @return \stdClass         stdClass of token and user's URI
     * @throws \RuntimeException When login fails
     */
    public function login(string $username, string $password, string $clientId, string $clientSecret)
    {
        $url    = $this->baseApiUrl . '/v2.1/token';
        $params = [
            'grant_type'    => 'password',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'username'      => $username,
            'password'      => $password,
        ];

        [$status, $result] = $this->apiPost($url, $params);
        // If login is successful, the API returns an object.
        // If it fails, it returns an array of strings with the error messages.
        if ($result) {
            try {
                $data = json_decode($result, false, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
            } catch (\JsonException $e) {
                // If json_decode fails, it means the response is not valid JSON.
                // We need to throw an exception with the error message.
                throw new \RuntimeException('Invalid JSON response: ' . $e->getMessage(), $e->getCode(), $e);
            }

            if (is_array($data)) {
                // If the result is an array, it means there was an error.
                // We need to throw an exception with the error messages.
                $errorMessage = implode(', ', $data);
                throw new \RuntimeException($errorMessage);
            }

            return $data;
        }
    }
}
