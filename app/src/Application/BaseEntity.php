<?php
namespace JoindIn\Web\Application;

use stdClass;

abstract class BaseEntity
{
    /** @var stdClass */
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
