<?php
namespace Application;

use stdClass;

abstract class BaseEntity
{
    protected $data;

    /**
     * BaseEntity constructor.
     * @param stdClass $data Model data retrieved from API
     */
    public function __construct(stdClass $data)
    {
        $this->data = $data;
    }
}
