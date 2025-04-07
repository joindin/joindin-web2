<?php
namespace Event;

use Talk\TalkApi;
use Talk\TalkEntity;

/**
 * Class EventScheduler
 *
 * Takes an event and constructs a data
 * structure to facilitate schedule layout view
 *
 */
class EventScheduler
{
    protected \Talk\TalkApi $talkApi;

    protected $distinctDates;

    /**
     * Constructor
     */
    public function __construct(TalkApi $talkApi)
    {
        $this->talkApi = $talkApi;
    }

    /**
     * Builds schedule data into an array structure
     * for schedule view
     */
    public function getScheduleData(EventEntity $eventEntity): array
    {
        $talks     = $this->getTalks($eventEntity->getTalksUri().'?start=0&resultsperpage=1000');

        return $this->getEventDays($talks);
    }

    /**
     * Retrieves talk collection from API
     *
     * @param string $talks_uri
     */
    public function getTalks($talks_uri): array
    {
        return $this->talkApi->getCollection($talks_uri);
    }

    /**
     * Get an array of populated EventSchedulerDay objects
     *
     * @param array $talks
     * @return array Array of EventSchedulerDay objects
     */
    public function getEventDays($talks): array
    {
        if (empty($talks) || empty($talks['talks'])) {
            return [];
        }

        $talks      = $talks['talks'];
        usort($talks, fn(TalkEntity $a, TalkEntity $b): int => $a->getStartDateTime() <=> $b->getStartDateTime() ?:
            ($a->getTracks() && $b->getTracks()
                ? strcasecmp($a->getTracks()[0]->track_uri, $b->getTracks()[0]->track_uri)
                : $a['id'] <=> $b['id']));

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
     * @param TalkEntity[] $talks
     */
    protected function organiseTalksByDayAndTime($talks): array
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
     */
    protected function getTracksByDay($talks): array
    {
        $tracksByDay = [];

        foreach ($talks as $talk) {
            $dateTime = $talk->getStartDateTime();
            $date     = $dateTime->format('d-m-Y');

            if (!isset($tracksByDay[$date]) || !array_key_exists($date, $tracksByDay)) {
                $tracksByDay[$date] = [];
            }

            $tracks = $talk->getTracks();

            if (is_array($tracks) && $tracks !== []) {
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
