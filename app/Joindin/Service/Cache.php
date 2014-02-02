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

    public function __construct($keyPrefix = '')
    {
		$settings = new Array('prefix'=>$keyPrefix);
		$this->client = new \Predis\Client($settings);
    }

	public function save($collection, $data, $keyField, $keyValue) {
		$fqKey = $collection.'-'.$keyField.'-'.substr(md5($keyValue), 0, 6);
		$this->client->set($fqKey, serialize($data));
	}

	public function load($collection, $keyField, $keyValue) {
		$fqKey = $collection.'-'.$keyField.'-'.substr(md5($keyValue), 0, 6);
		$data = unserialize($this->client->get($fqKey));
		return $data;
	}

	public function saveByKeys($collection, $data, array $keys) {
		$fqKey = $collection;
		foreach ($keys as $keyField=>$keyValue) {
			$fqKey.= '-'.$keyField.'-'.substr(md5($keyValue), 0, 6);
		}
		$this->client->set($fqKey, serialize($data));
	}

	public function loadByKeys($collection, array $keys) {
		$fqKey = $collection;
		foreach ($keys as $keyField=>$keyValue) {
			$fqKey.= '-'.$keyField.'-'.substr(md5($keyValue), 0, 6);
		}
		$data = unserialize($this->client->get($fqKey));
		return $data;
	}

}

