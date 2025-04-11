<?php
namespace Application;

abstract class BaseDb
{
    protected CacheService $cache;

    protected string $keyName;

    public function __construct(CacheService $cacheService)
    {
        $this->cache = $cacheService;
    }

    public function load(string $keyField, $keyValue)
    {
        return $this->cache->load($this->keyName, $keyField, $keyValue);
    }
}
