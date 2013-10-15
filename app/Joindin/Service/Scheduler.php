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
     * @param \Joindin\Model\Event $event
     * @return mixed
     * @throws \Exception
     */
    public function getScheduleData(\Joindin\Model\Event $event)
    {
        $talks = $this->getTalks($event->getTalksUri().'?start=0&resultsperpage=1000');
        $eventDays = $this->getEventDays($talks);

        return $eventDays;
    }

    /**
     * Retrieves talk collection from API
     *
     * @param $talks_uri
     * @return \Joindin\Model\Talk
     */
    public function getTalks($talks_uri)
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
    public function getEventDays($talks)
    {
        if(empty($talks)) {
            return array();
        }

        $talks = $talks['talks'];
        $talksByDay = $this->organiseTalksByDayAndTime($talks);

        $tracksByDay = $this->getTracksByDay($talks);

        $eventDays = array();
        foreach ($talksByDay as $date => $talks) {
            $eventDays[] = new \Joindin\Service\Helper\EventDay($date, $talks, $tracksByDay[$date]);
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
    protected function organiseTalksByDayAndTime($talks)
    {
        $talksByDay = array();

        foreach ($talks as $talk) {
            $dateTime = $talk->getStartDateTime();
            $date = $dateTime->format('d-m-Y');
            $time = $dateTime->format('H:i');

            if (!isset($talksByDay[$date]) || !array_key_exists($date, $talksByDay)) {
                $talksByDay[$date] = array();

            }
            if (!isset($talksByDay[$date][$time]) || !array_key_exists($time, $talksByDay[$date])) {
                $talksByDay[$date][$time] = array();
            }

            $talksByDay[$date][$time][] = $talk;
        }

        return $talksByDay;
    }

    /**
     * Get a multi-dimensional indexed array of unique
     * track names by date
     *
     * @param $talks
     * @return array
     */
    protected function getTracksByDay($talks)
    {
        $tracksByDay = array();

        foreach ($talks as $talk) {

            $dateTime = $talk->getStartDateTime();
            $date = $dateTime->format('d-m-Y');

            if (!isset($tracksByDay[$date]) || !array_key_exists($date, $tracksByDay)) {
                $tracksByDay[$date] = array();
            }

            $tracks = $talk->getTracks();

            if (is_array($tracks) && count($tracks > 0)) {
                foreach ($tracks as $track) {
                    //obtain array of unique track names as array key
                    $tracksByDay[$date][$track->track_name] = true;
                }
            }
        }

        //set unique track names gathered above as array values
        foreach ($tracksByDay as $date => $tracks) {
            $tracksByDay[$date] = array_keys($tracks);
        }

        return $tracksByDay;
    }
}
