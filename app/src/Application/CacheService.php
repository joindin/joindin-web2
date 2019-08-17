<?php
namespace Application;

/**
 * Class CacheService
 *
 * Stores semi-transiently a piece of data against a given key.
 */
class CacheService
{
    protected $client;
    protected $keyPrefix;

    public function __construct(\Predis\Client $client, $keyPrefix = '')
    {
        $this->keyPrefix = $keyPrefix;
        $this->client    = $client;
    }

    public function save($collection, $data, $keyField, $keyValue)
    {
        $fqKey = $this->keyPrefix . $collection . '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);
        $this->client->set($fqKey, serialize($data));
    }

    public function load($collection, $keyField, $keyValue)
    {
        $fqKey = $this->keyPrefix . $collection . '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);

        return unserialize($this->client->get($fqKey));
    }

    public function saveByKeys($collection, $data, array $keys)
    {
        $fqKey = $this->keyPrefix . $collection;
        foreach ($keys as $keyField => $keyValue) {
            $fqKey .= '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);
        }
        $this->client->set($fqKey, serialize($data));
    }

    public function loadByKeys($collection, array $keys)
    {
        $fqKey = $this->keyPrefix . $collection;
        foreach ($keys as $keyField => $keyValue) {
            $fqKey .= '-' . $keyField . '-' . substr(md5($keyValue), 0, 6);
        }

        return unserialize($this->client->get($fqKey));
    }
}
