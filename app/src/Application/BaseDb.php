<?php
namespace Application;

class BaseDb
{
    protected $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }
}
