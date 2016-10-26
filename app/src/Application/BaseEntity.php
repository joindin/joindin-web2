<?php
namespace Application;

abstract class BaseEntity
{
    protected $data;

    /**
     * BaseEntity constructor.
     * @param \stdClass $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}
