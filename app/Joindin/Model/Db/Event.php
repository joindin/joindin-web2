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

    public function save(\Joindin\Model\Event $event)
    {
        $data = array(
            "url_friendly_name" => $event->getUrlFriendlyName(),
            "uri" => $event->getUri(),
            "stub" => $event->getStub(),
            "verbose_uri" => $event->getVerboseUri()
        );

        $this->cache->save('events', $data, 'uri', $event->getUri());
        $this->cache->save('events', $data, 'url_friendly_name', $event->getUrlFriendlyName());
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
