<?php
namespace Joindin\Model\Db;

class Event
{
    protected $keyName = 'events';
    protected $cache;

    public function __construct(\Joindin\Service\Cache $cache)
    {
        $this->cache = $cache;
    }

    public function load($keyField, $keyValue)
    {
		return $this->cache->load('events', $keyField, $keyValue);
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
        $this->cache->save('events', $data, 'stub', $event->getStub());
    }

}
