<?php
namespace User;

use Application\baseDb;

class UserDb extends BaseDb
{
    protected $keyName = 'users';

    public function load($uri)
    {
        $data = $this->cache->load($this->keyName, 'uri', $uri);
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

        return $this->cache->save($this->keyName, $data, 'uri', $user->getUri());
    }
}
