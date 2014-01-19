<?php
namespace Joindin\Model\Db;

use  \Joindin\Service\Cache as CacheService;

class Event
{
    protected $keyName = 'events';
    protected $db;

    public function __construct($dbNum)
    {
        $this->cache = new CacheService($dbNum);
    }

    public function getUriFor($slug)
    {
        $data = $this->db->getOneByKey($this->keyName, 'slug', $slug);
        return $data['uri'];
    }

    public function load($collection, $keyField, $keyValue)
    {
		return $this->cache->load($collection, $keyField, $keyValue);
    }

    public function save($collection, $data, $keyField, $keyValue)
    {
		return $this->cache->save($collection, $data, $keyField, $keyValue);
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
