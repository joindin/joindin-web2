<?php
namespace Joindin\Model\Db;

use  \Joindin\Service\Cache as Cache;

class User
{
    protected $keyName = 'users';
    protected $cache;

    public function __construct($keyPrefix)
    {
        $this->cache = new Cache($keyPrefix);
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
