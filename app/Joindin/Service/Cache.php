<?php
namespace Joindin\Service;

/**
 * Class Cache
 *
 * Stores semi-transiently a piece of data against a given key.
 *
 * @package Joindin\Service
 */
class Cache
{
    protected $client;

    public function __construct($dbIndex = false)
    {
		$this->client = new \Predis\Client();
		if ($dbIndex !== false) {
			if (is_numeric($dbIndex)) {
				$this->client->select($dbIndex);
			} else {
				throw new Exception("Invalid config (".var_export($dbIndex, true).") for Redis DB index, should be numeric");
			}
		}
    }

	public function save($collection, $data, $key) {
		$fqKey = $collection.'-'.md5($key);
		$this->client->set($fqKey, serialize($data));
	}

	public function getOneByKey($collection, $key) {
		$fqKey = $collection.'-'.md5($key);
		$data = unserialize($this->client->get($fqKey));
		return $data;
	}

}

