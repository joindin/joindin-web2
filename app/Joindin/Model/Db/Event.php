<?php
namespace Joindin\Model\Db;

use  \Joindin\Service\Db as DbService;

class Event
{
    protected $keyName = 'events';
    protected $db;

    public function __construct()
    {
        $this->db = new DbService();
    }

    public function getUriFor($slug)
    {
        $data = $this->db->getOneByKey($this->keyName, 'slug', $slug);
        return $data['uri'];
    }

    public function load($uri)
    {
        $data = $this->db->getOneByKey($this->keyName, 'uri', $uri);
        return $data;
    }

    public function saveSlugToDatabase(\Joindin\Model\Event $event)
    {
        $data = array(
            'uri'  => $event->getUri(),
            'name' => $event->getName(),
            'slug' => $event->getSlug(),
            'verbose_uri'  => $event->getVerboseUri(),
        );

        $mongoEvent = $this->load($event->getUri());
        if ($mongoEvent) {
            // event is already known - update this record
            $data = array_merge($mongoEvent, $data);
        }

        return $this->db->save($this->keyName, $data);
    }
}
