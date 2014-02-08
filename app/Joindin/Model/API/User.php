<?php
namespace Joindin\Model\API;

use Joindin\Model\User as UserEntity;

class User extends \Joindin\Model\API\JoindIn
{

    public function __construct($config, $accessToken, \Joindin\Model\Db\User $userDb)
        parent::__construct($configObj, $accessToken);

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
}
