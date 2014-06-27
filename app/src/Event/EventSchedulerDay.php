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
     * @var Timestamp $startTime
     */
    private $startTime;

    /**
     * @var Timestamp $endTime
     */
    private $endTime;

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

        foreach ($talks as $time => $time_talks) {
            if (!isset($this->startTime)) {
                $this->startTime = $this->endTime = strtotime("$date $time");
            }
            foreach ($time_talks as $talk) {
                $duration = $talk->getDuration();
                if (empty($duration)) $duration = 45;
                $end_time = strtotime("$date $time +$duration minutes");
                if ($end_time > $this->endTime) $this->endTime = $end_time;
            }
        }
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

    /**
     * Get startTime
     *
     * @return Timestamp
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Get endTime
     *
     * @return Timestamp
     */
    public function getEndTime()
    {
        return $this->endTime;
    }
}
