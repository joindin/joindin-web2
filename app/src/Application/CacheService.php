<?php
namespace Application;

/**
 * Class CacheService
 *
 * Stores semi-transiently a piece of data against a given key.
 */
class CacheService
{
    protected \Predis\Client $client;
    protected $keyPrefix;

    public function __construct(\Predis\Client $client, $keyPrefix = '')
    {
        $this->keyPrefix = $keyPrefix;
        $this->client    = $client;
    }

    public function save(string $collection, $data, string $keyField, $keyValue): void
    {
        $fqKey = $this->keyPrefix . $collection . '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);
        $this->client->set($fqKey, serialize($data));
    }

    public function load(string $collection, string $keyField, $keyValue)
    {
        $fqKey = $this->keyPrefix . $collection . '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);

        return unserialize($this->client->get($fqKey));
    }

    public function saveByKeys(string $collection, $data, array $keys): void
    {
        $fqKey = $this->keyPrefix . $collection;
        foreach ($keys as $keyField => $keyValue) {
            $fqKey .= '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);
        }
        $this->client->set($fqKey, serialize($data));
    }

    public function loadByKeys(string $collection, array $keys)
    {
        $fqKey = $this->keyPrefix . $collection;
        foreach ($keys as $keyField => $keyValue) {
            $fqKey .= '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);
        }

        return unserialize($this->client->get($fqKey));
    }
}
