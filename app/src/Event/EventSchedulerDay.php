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
     * @var string
     */
    private $date;

    /**
     * @var array
     */
    private $talks;

    /**
     * @var array
     */
    private $tracks;

    /**
     * Constructor
     *
     * @param string $date
     * @param array $talks  Multi-dimensional associative array of talk objects
     * @param array $tracks Indexed array of track names
     */
    public function __construct($date, $talks, $tracks)
    {
        $this->date   = $date;
        $this->talks  = $talks;
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
     * @return array
     */
    public function getTalks()
    {
        return $this->talks;
    }

    /**
     * Get tracks
     *
     * @return array
     */
    public function getTracks()
    {
        return $this->tracks;
    }
}
