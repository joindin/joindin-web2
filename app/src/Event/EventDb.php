<?php
namespace Event;

use Application\BaseDb;
use Application\CacheService;

class EventDb extends BaseDb
{
    public function __construct(CacheService $cache)
    {
        parent::__construct($cache);
        $this->keyName = 'events';
    }

    public function save(EventEntity $event)
    {
        $data = [
            "url_friendly_name" => $event->getUrlFriendlyName(),
            "uri"               => $event->getUri(),
            "stub"              => $event->getStub(),
            "verbose_uri"       => $event->getVerboseUri(),
            "name"              => $event->getName(),
        ];

        $this->cache->save($this->keyName, $data, 'uri', $event->getUri());
        $this->cache->save($this->keyName, $data, 'url_friendly_name', $event->getUrlFriendlyName());
        $this->cache->save($this->keyName, $data, 'stub', $event->getStub());
    }
}
