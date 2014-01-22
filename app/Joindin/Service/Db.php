<?php
namespace Joindin\Service;

class Db
{
    private $_dbclass;
    protected $databaseName;

    public function __construct($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * Set a mock MongoClient class
     *
     * @param MongoClient $client MongoClient class to mock
     *
     * @return MongoClient class
     */
    public function setMongoClient($client)
    {
        $this->_dbclass = $client;
        return $client;
    }

    /**
     * Get a single value from key $key
     *
     * @param string $collection Collection to search
     * @param string $key        Key to search
     * @param string $value      Value to match
     *
     * @return object
     */
    public function getOneByKey($collection, $key, $value)
    {
        return $this->_getMongoClient()
            ->selectCollection($this->databaseName, $collection)
            ->findOne(array($key => $value));
    }

    /**
     * Get a single talk from Key/Value paired array
     *
     * @param $collection
     * @param array $keyValues
     * @return array|null
     */
    public function getOneByKeys($collection, array $keyValues)
    {
        $record = $this->_getMongoClient()
            ->selectCollection($this->databaseName, $collection)
            ->findOne($keyValues);

        return $record;
    }

    /**
     * Save a value
     *
     * @param string $collection Collection to save into
     * @param array  $data       Data to save
     * @param array  $criteria   The data to update on
     *
     * @return array Status of save
     */
    public function save($collection, $data, $criteria)
    {
        return $this->_getMongoClient()
            ->selectCollection($this->databaseName, $collection)
            ->update($criteria, $data, array("upsert" => true));
    }


    /**
     * Returns MongoClient object, or a mock
     *
     * @return \MongoClient
     */
    private function _getMongoClient()
    {
        if (null == $this->_dbclass) {
            $db = new \MongoClient();
            $this->_dbclass = $db;
        }

        return $this->_dbclass;
    }
}
