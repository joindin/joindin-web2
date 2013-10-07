<?php
namespace Joindin\Model\API;

class User extends \Joindin\Model\API\JoindIn
{
    public function getUser($url)
    {
        $result = $this->apiGet($url, array('verbose'=>'yes'));

        if ($result) {
            $data = json_decode($result);
            if ($data) {
                if (isset($data->users) && isset($data->users[0])) {
                    $user = $data->users[0];
                    return $user;
                }

            }
        }
        return false;
    }

}
