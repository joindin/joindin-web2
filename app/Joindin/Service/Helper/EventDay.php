<?php
namespace Joindin\Service\Helper;

/**
 * Class EventDay
 *
 * A helper class to support the scheduler service
 *
 * @package Joindin\Service\Helper
 */
class EventDay
{
    private $date;
    private $talks;

    /**
     * Constructor
     *
     * @param $date String
     * @param $talks Array Multi-dimensional associative array of talk objects
     */
    public function __construct($date, $talks)
    {
        $this->date = $date;
        $this->talks = $talks;
    }

    public function getDate()
    {
        $date = new \DateTime($this->date);
        return $date->format('l, jS F Y');
    }

    public function getTalks()
    {
        return $this->talks;
    }
}
