<?php
namespace Application;

class BaseDb
{
    protected $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function load($keyField, $keyValue)
    {
        return $this->cache->load($this->keyName, $keyField, $keyValue);
    }
}
