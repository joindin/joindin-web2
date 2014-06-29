<?php
namespace Event;

use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Joindin\Api\Entity\Event;
use Joindin\Api\Response;

class EventApi
{
    /** @var EventDb */
    protected $cache;

    /** @var GuzzleClient */
    protected $eventService;

    /** @var GuzzleClient */
    protected $eventCommentService;

    /**
     * Constructs a new API Client and initializes the associated services.
     *
     * @param EventDb      $cache
     * @param GuzzleClient $eventService
     * @param GuzzleClient $eventCommentService
     */
    public function __construct(EventDb $cache, GuzzleClient $eventService, GuzzleClient $eventCommentService)
    {
        $this->cache               = $cache;
        $this->eventService        = $eventService;
        $this->eventCommentService = $eventCommentService;
    }

    /**
     * Get the latest events
     *
     * @param integer $limit  Number of events to get per page
     * @param integer $start  Start value for pagination
     * @param string  $filter Filter to apply
     *
     * @return array
     */
    public function getCollection($limit = 10, $start = 1, $filter = null)
    {
        /** @var Response $response */
        $response = $this->eventService->getCollection(
            array('resultsperpage' => $limit, 'start' => $start, 'filter' => $filter)
        );

        /** @var Event[] $events */
        $events = $response->getResource();
        $this->storeEventsInCache($events);

        return array('events' => $events, 'pagination' => $response->getMeta());
    }

    /**
     * Look up this friendlyUrl in the DB, get an API endpoint, fetch data and return us an event.
     *
     * @param string $friendlyUrl The nice url bit of the event (e.g. phpbenelux-conference-2014)
     *
     * @return Event|null The event we found, or false if something went wrong
     */
    public function getByFriendlyUrl($friendlyUrl)
    {
        return $this->fetchEventFromDbByProperty('url_friendly_name', $friendlyUrl);
    }

    /**
     * Look up this stub in the DB, get an API endpoint, fetch data and return us an event.
     *
     * @param string $stub The short url bit of the event (e.g. phpbnl14)
     *
     * @return Event|null The event we found, or false if something went wrong
     */
    public function getByStub($stub)
    {
        return $this->fetchEventFromDbByProperty('stub', $stub);
    }

    /**
     * Retrieves an event using its API url.
     *
     * @param string $url
     *
     * @return Event|null
     */
    public function getByUrl($url)
    {
        $event = $this->fetchEventFromApi($url);
        if (! $event) {
            return null;
        }

        // store event in cache to be able to retrieve it with the other methods as well.
        $this->storeEventsInCache(array($event));

        return $event;
    }

	/**
	 * Get comments for given event comment uri.
     *
	 * @param string $commentUri
     *
	 * @return Event\Comment[]
	 */
    public function getComments($commentUri)
    {
        /** @var Response $response */
        $response = $this->eventCommentService->getCollection(array('url' => $commentUri));

        return $response->getResource();
    }

    /**
     * Adds a comment on the given event.
     *
     * @param Event  $event
     * @param string $comment
     *
     * @throws \Exception if the API failed to submit the new comment.
     *
     * @return void
     */
    public function addComment(Event $event, $comment)
    {
        try {
            $this->eventCommentService->submit(array('url' => $event->getCommentsUri(), 'comment' => $comment));
        } catch (\Exception $e) {
            throw new \Exception('Failed to add comment', 0, $e);
        }
    }

    /**
     * Marks the current user as attending the given event.
     *
     * @param Event $event
     *
     * @throws \Exception if the API failed to accept this request.
     *
     * @return void
     */
    public function attend(Event $event)
    {
        try {
            $this->eventService->getHttpClient()->post($event->getAttendingUri());
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark you as attending', 0, $e);
        }
    }

    /**
     * Submits a new event to the API and returns it or null if it is pending acceptance.
     *
     * @param array $data
     *
     * @throws \Exception if a status code other than 201 is returned.
     *
     * @see EventFormType::buildForm() for a list of supported fields in the $data array and their constraints.
     *
     * @return Event|null
     */
    public function submit(array $data)
    {
        $data = $this->splitTimezone($data);
        $data = $this->convertDateTimeFieldsToString($data);

        try {
            $response = $this->eventService->submit($data);
        } catch (\Exception $e) {
            throw new \Exception('Your event submission was not accepted, the server reports: ' . $e->getMessage());
        }

        // if the response is empty or an empty array; then the event was pending
        if (! $response) {
            return null;
        }

        return $this->getByUrl($response['location']);
    }

    /**
     * Queries the cache with the given key and value and fetches the event using the returned URL.
     *
     * @param string $key
     * @param string $value
     *
     * @return Event|null
     */
    private function fetchEventFromDbByProperty($key, $value)
    {
        $event = $this->cache->load($key, $value);
        if (! $event) {
            return null;
        }

        return $this->fetchEventFromApi($event['uri']);
    }

    /**
     * Attempts to fetch an event from the API.
     *
     * @param string $url
     *
     * @return Event|null
     */
    private function fetchEventFromApi($url)
    {
        try {
            /** @var Response $response */
            $response = $this->eventService->fetch(array('url' => $url));
        } catch (\Exception $e) {
            return null;
        }

        return current($response->getResource());
    }

    /**
     * Stores the events in the Event Cache.
     *
     * @param Event[] $events
     *
     * @return void
     */
    private function storeEventsInCache(array $events)
    {
        foreach ($events as $event) {
            $this->cache->save($event);
        }
    }

    /**
     * Checks submitted data for a timezone field and split it into tz_continent and tz_place.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function splitTimezone(array $data)
    {
        if (! isset($data['timezone'])) {
            return $data;
        }

        list($tz_continent, $tz_place) = explode('/', $data['timezone']);
        unset($data['timezone']);
        $data['tz_continent'] = $tz_continent;
        $data['tz_place'] = $tz_place;

        return $data;
    }

    /**
     * Convert datetime objects to their string representation when submitting data.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function convertDateTimeFieldsToString(array $data)
    {
        $dateFields = array('start_date', 'end_date', 'cfp_start_date', 'cfp_end_date');

        foreach ($dateFields as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof \DateTime) {
                $data[$dateField] = $data[$dateField]->format('Y-m-d');
            }
        }

        return $data;
    }
}
