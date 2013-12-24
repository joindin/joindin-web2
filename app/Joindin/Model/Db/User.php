<?php
namespace Joindin\Model\Db;

use  \Joindin\Service\Db as DbService;

class User
{
    protected $keyName = 'users';
    protected $db;

    public function __construct()
    {
        $this->db = new DbService();
    }

    public function getUriFor($username)
    {
        $data = $this->db->getOneByKey($this->keyName, 'username', $username);
        return $data['uri'];
    }

    public function load($uri)
    {
        $data = $this->db->getOneByKey($this->keyName, 'uri', $uri);
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

        $mongoUser = $this->load($user->getUri());
        if ($mongoUser) {
            // user is already known - update this record
            $data = array_merge($mongoUser, $data);
        }

        return $this->db->save($this->keyName, $data);
    }
}
