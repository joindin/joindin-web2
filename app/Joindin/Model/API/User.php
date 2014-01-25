<?php
/*
 * PROBLEMS LOGGING IN?
 *
 * If you want to use User related features, you will need to use a locally
 * checked out copy of the API: you cannot log into the live API
 */

namespace Joindin\Model\API;

use Joindin\Model\User as UserEntity;
use Joindin\Model\Db\User as UserDb;

class User extends \Joindin\Model\API\JoindIn
{

    protected $mongoDatabaseName;

    public function __construct($configObj, $accessToken) {
        parent::__construct($configObj, $accessToken);

        $config = $configObj->getConfig();
        if (isset($config['mongo']) && isset($config['mongo']['database_name'])) {
            $this->mongoDatabaseName = $config['mongo']['database_name'];
        }

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

                    // store to database for later
                    $db = new UserDb($this->mongoDatabaseName);
                    $db->saveSlugToDatabase($user);
                    return $user;
                }

            }
        }
        return false;
    }
}
