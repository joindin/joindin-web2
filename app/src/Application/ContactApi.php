<?php
namespace Application;

use Application\BaseApi;

class ContactApi extends BaseApi
{
    public function contact($name, $email, $subject, $comment, $clientId, $clientSecret)
    {
        $url    = $this->baseApiUrl . '/v2.1/contact';
        $params = [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'name'          => $name,
            'email'         => $email,
            'subject'       => $subject,
            'comment'       => $comment,
        ];

        list($status, $result) = $this->apiPost($url, $params);

        if ($status == 202) {
            return true;
        }

        $result  = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed: " . $message);
    }
}
