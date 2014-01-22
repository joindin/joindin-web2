<?php
namespace Joindin\Model\API;

use Joindin\Model\User as UserEntity;
use Joindin\Model\Db\User as UserDb;

class User extends \Joindin\Model\API\JoindIn
{
    /**
     * Retreive user information from the API
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

                    // store to database for later
                    $db = new UserDb();
                    $db->saveSlugToDatabase($user);
                    return $user;
                }

            }
        }
        return false;
    }
}
