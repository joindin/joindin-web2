<?php
namespace User;

use Application\BaseDb;
use Application\CacheService;

class UserDb extends BaseDb
{
    public function __construct(CacheService $cacheService)
    {
        parent::__construct($cacheService);
        $this->keyName = 'users';
    }

    public function save(UserEntity $userEntity): void
    {
        $data = [
            'uri'          => $userEntity->getUri(),
            'username'     => $userEntity->getUsername(),
            'slug'         => $userEntity->getUsername(),
            'verbose_uri'  => $userEntity->getVerboseUri()
        ];

        $savedUser = $this->load('uri', $userEntity->getUri());
        if ($savedUser) {
            // user is already known - update this record
            $data = array_merge($savedUser, $data);
        }

        $this->cache->save($this->keyName, $data, 'uri', $userEntity->getUri());
        $this->cache->save($this->keyName, $data, 'username', $userEntity->getUsername());
    }
}
