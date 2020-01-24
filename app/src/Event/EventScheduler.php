<?php
namespace Event;

use Talk\TalkApi;

/**
 * Class EventScheduler
 *
 * Takes an event and constructs a data
 * structure to facilitate schedule layout view
 *
 */
class EventScheduler
{
    protected $talkApi;
    protected $distinctDates;

    /**
     * Constructor
     *
     * @param TalkApi $talkApi
     */
    public function __construct(TalkApi $talkApi)
    {
        $this->talkApi = $talkApi;
    }

    /**
     * Builds schedule data into an array structure
     * for schedule view
     *
     * @param EventEntity $event
     * @return array
     */
    public function getScheduleData(EventEntity $event)
    {
        $talks     = $this->getTalks($event->getTalksUri().'?start=0&resultsperpage=1000');
        $eventDays = $this->getEventDays($talks);

        return $eventDays;
    }

    /**
     * Retrieves talk collection from API
     *
     * @param string $talks_uri
     * @return array
     */
    public function getTalks($talks_uri)
    {
        $talks = $this->talkApi->getCollection($talks_uri);

        return $talks;
    }

    /**
     * Get an array of populated EventSchedulerDay objects
     *
     * @param array $talks
     * @return array Array of EventSchedulerDay objects
     */
    public function getEventDays($talks)
    {
        if (empty($talks) || empty($talks['talks'])) {
            return [];
        }

        $talks      = $talks['talks'];
        $talksByDay = $this->organiseTalksByDayAndTime($talks);

        $tracksByDay = $this->getTracksByDay($talks);

        $eventDays = [];
        foreach ($talksByDay as $date => $talks) {
            $eventDays[] = new EventSchedulerDay($date, $talks, $tracksByDay[$date]);
        }

        return $eventDays;
    }

    /**
     * Organise event talks into an multi-dimensional
     * associative array by day, then by time for each
     * day
     *
     * @param array $talks
     * @return array
     */
    protected function organiseTalksByDayAndTime($talks)
    {
        $talksByDay = [];

        foreach ($talks as $talk) {
            $dateTime = $talk->getStartDateTime();
            $date     = $dateTime->format('d-m-Y');
            $time     = $dateTime->format('H:i');

            if (!isset($talksByDay[$date]) || !array_key_exists($date, $talksByDay)) {
                $talksByDay[$date] = [];
            }
            if (!isset($talksByDay[$date][$time]) || !array_key_exists($time, $talksByDay[$date])) {
                $talksByDay[$date][$time] = [];
            }

            $talksByDay[$date][$time][] = $talk;
        }

        return $talksByDay;
    }

    /**
     * Get a multi-dimensional indexed array of unique
     * track names by date
     *
     * @param array $talks
     * @return array
     */
    protected function getTracksByDay($talks)
    {
        $tracksByDay = [];

        foreach ($talks as $talk) {
            $dateTime = $talk->getStartDateTime();
            $date     = $dateTime->format('d-m-Y');

            if (!isset($tracksByDay[$date]) || !array_key_exists($date, $tracksByDay)) {
                $tracksByDay[$date] = [];
            }

            $tracks = $talk->getTracks();

            if (is_array($tracks) && !empty($tracks)) {
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
