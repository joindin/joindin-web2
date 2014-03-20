<?php
namespace Event;

use DateTime;

/**
 * Class EventSchedulerDay
 *
 * A helper class to support the scheduler service
 *
 */
class EventSchedulerDay
{
    /**
     * @var DateTime $date
     */
    private $date;

    /**
     * @var Array $talks
     */
    private $talks;

    /**
     * @var Array $tracks
     */
    private $tracks;

    /**
     * Constructor
     *
     * @param $date String
     * @param $talks Array Multi-dimensional associative array of talk objects
     * @param $tracks Array Indexed array of track names
     */
    public function __construct($date, $talks, $tracks)
    {
        $this->date = $date;
        $this->talks = $talks;
        $this->tracks = $tracks;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        $date = new DateTime($this->date);

        return $date->format('l, jS F Y');
    }

    /**
     * Get talks
     *
     * @return Array
     */
    public function getTalks()
    {
        return $this->talks;
    }

    /**
     * Get tracks
     *
     * @return Array
     */
    public function getTracks()
    {
        return $this->tracks;
    }
}
