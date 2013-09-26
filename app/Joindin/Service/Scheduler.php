<?php
namespace Joindin\Service;

/**
 * Class Scheduler
 *
 * Takes an event and constructs a data
 * structure to facilitate schedule layout view
 *
 * @package Joindin\Service
 */
class Scheduler
{
    protected $talkApi;
    protected $distinctDates;

    /**
     * Constructor
     *
     * @param \Joindin\Model\API\Talk $apiTalk
     */
    public function __construct(\Joindin\Model\API\Talk $apiTalk)
    {
        $this->apiTalk = $apiTalk;
    }

    /**
     * Builds schedule data into an array structure
     * for schedule view
     *
     * @param \Joindin\Model\API\Event $event
     * @return mixed
     * @throws \Exception
     */
    function getScheduleData(\Joindin\Model\API\Event $event)
    {
        $talks = $this->getTalks($event->getTalksUri());
        $eventDays = $this->getEventDays($talks);

        return $eventDays;
    }

    /**
     * Retrieves talk collection from API
     *
     * @param $talks_uri
     * @return \Joindin\Model\Talk
     */
    function getTalks($talks_uri)
    {
        $talks = $this->apiTalk->getCollection($talks_uri);

        return $talks;
    }

    /**
     * Get an array of populated EventDay objects
     *
     * @param $talks
     * @return array Array of EventDay objects
     */
    function getEventDays($talks)
    {
        if(empty($talks)) {
            return array();
        }

        $talksByDay = $this->organiseTalksByDayAndTime($talks);

        $eventDays = array();
        foreach ($talksByDay as $date => $talks) {
            $eventDays[] = new \Joindin\Service\Helper\EventDay($date, $talks['talks']);
        }

        return $eventDays;
    }

    /**
     * Organise event talks into an multi-dimensional
     * associative array by day, then by time for each
     * day
     *
     * @param $talks
     * @return array
     */
    function organiseTalksByDayAndTime($talks)
    {
        $talks = $talks['talks'];

        $talksByDay = array();
        foreach ($talks as $talk) {
            $dateTime = $talk->getStartDateTime();
            $date = $dateTime->format('d-m-Y');
            $time = $dateTime->format('H:i');

            if (!isset($talksByDay[$date]) || !array_key_exists($date, $talksByDay)) {
                $talksByDay[$date] = array();
                $talksByDay[$date]['talks'] = array();
            }
            if (!isset($talksByDay[$date]['talks'][$time]) || !array_key_exists($time, $talksByDay[$date]['talks'])) {
                $talksByDay[$date]['talks'][$time] = array();
            }

            $talksByDay[$date]['talks'][$time][] = $talk;
        }

        //sort each day's talks by time
        foreach ($talksByDay as $date => $talks) {
            ksort($talksByDay[$date]['talks'], SORT_NUMERIC);
        }

        return $talksByDay;
    }
}
