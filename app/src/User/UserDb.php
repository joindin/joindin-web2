<?php
namespace User;

use Application\BaseDb;

class UserDb extends BaseDb
{
    protected $keyName = 'users';

    public function save(UserEntity $user)
    {
        $data = array(
            'uri'  => $user->getUri(),
            'username' => $user->getUsername(),
            'slug' => $user->getUsername(),
            'verbose_uri'  => $user->getVerboseUri()
        );

        $savedUser = $this->load('uri', $user->getUri());
        if ($savedUser) {
            // user is already known - update this record
            $data = array_merge($savedUser, $data);
        }

        $this->cache->save($this->keyName, $data, 'uri', $user->getUri());
        $this->cache->save($this->keyName, $data, 'username', $user->getUsername());
    }
}
