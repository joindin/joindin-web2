<?php
namespace Application;

abstract class BaseDb
{
    protected \Application\CacheService $cache;

    /** @var string */
    protected $keyName;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function load(string $keyField, $keyValue)
    {
        return $this->cache->load($this->keyName, $keyField, $keyValue);
    }
}
