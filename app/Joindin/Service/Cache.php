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

	public function save($collection, $data, $keyField, $keyValue) {
		$fqKey = $collection.'-'.$keyField.'-'.md5($keyValue);
		$this->client->set($fqKey, serialize($data));
	}

	public function load($collection, $keyField, $keyValue) {
		$fqKey = $collection.'-'.$keyField.'-'.md5($keyValue);
		$data = unserialize($this->client->get($fqKey));
		return $data;
	}

	public function saveByKeys($collection, $data, array $keys) {
		$fqKey = $collection;
		foreach ($keys as $keyField=>$keyValue) {
			$fqKey.= '-'.$keyField.'-'.md5($keyValue);
		}
		$this->client->set($fqKey, serialize($data));
	}

	public function loadByKeys($collection, array $keys) {
		$fqKey = $collection;
		foreach ($keys as $keyField=>$keyValue) {
			$fqKey.= '-'.$keyField.'-'.md5($keyValue);
		}
		$data = unserialize($this->client->get($fqKey));
		return $data;
	}

}

