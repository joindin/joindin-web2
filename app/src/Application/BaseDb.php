<?php
namespace JoindIn\Web\Application;

use JoindIn\Web\Application\CacheService;

abstract class BaseDb
{
    /** @var CacheService */
    protected $cache;

    /** @var string */
    protected $keyName;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function load($keyField, $keyValue)
    {
        return $this->cache->load($this->keyName, $keyField, $keyValue);
    }
}
