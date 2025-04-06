<?php
namespace Event;

use Application\BaseDb;
use Application\CacheService;

class EventDb extends BaseDb
{
    public function __construct(CacheService $cacheService)
    {
        parent::__construct($cacheService);
        $this->keyName = 'events';
    }

    public function save(EventEntity $eventEntity): void
    {
        $data = [
            "url_friendly_name" => $eventEntity->getUrlFriendlyName(),
            "uri"               => $eventEntity->getUri(),
            "stub"              => $eventEntity->getStub(),
            "verbose_uri"       => $eventEntity->getVerboseUri(),
            "name"              => $eventEntity->getName(),
        ];

        $this->cache->save($this->keyName, $data, 'uri', $eventEntity->getUri());
        $this->cache->save($this->keyName, $data, 'url_friendly_name', $eventEntity->getUrlFriendlyName());
        $this->cache->save($this->keyName, $data, 'stub', $eventEntity->getStub());
    }
}
