<?php
namespace Joindin\Model\Db;

use  \Joindin\Service\Cache as CacheService;

class User
{
    protected $keyName = 'users';
    protected $db;

    public function __construct()
    {
        $this->cache = new CacheService();
    }

    public function getUriFor($username)
    {
        $data = $this->db->getOneByKey($this->keyName, 'username', $username);
        return $data['uri'];
    }

    public function load($uri)
    {
        $data = $this->cache->load('users', 'uri', $uri);
        return $data;
    }

    public function saveSlugToDatabase($user)
    {
        $data = array(
            'uri'  => $user->getUri(),
            'username' => $user->getUsername(),
            'slug' => $user->getUsername(),
            'verbose_uri'  => $user->getVerboseUri()
        );

        $savedUser = $this->load($user->getUri());
        if ($savedUser) {
            // user is already known - update this record
            $data = array_merge($savedUser, $data);
        }

        return $this->cache->save('users', $data, 'uri', $user->getUri());
    }
}
