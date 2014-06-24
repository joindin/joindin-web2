<?php
namespace Event;

use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Joindin\Api\Client;
use Joindin\Api\Description\Event\Comments;
use Joindin\Api\Description\Events;
use Joindin\Api\Entity\Event;
use Joindin\Api\Response;

class EventApi
{
    /** @var EventDb */
    protected $eventDb;

    /** @var GuzzleClient  */
    protected $eventService;

    /** @var GuzzleClient  */
    protected $eventCommentService;

    public function __construct(array $config, $accessToken, EventDb $eventDb)
    {
        $apiClient = new Client(
            array(
                'base_url'     => $config['apiUrl'],
                'access_token' => $accessToken
            )
        );

        $this->eventService        = $apiClient->getService(new Events());
        $this->eventCommentService = $apiClient->getService(new Comments());
        $this->eventDb             = $eventDb;
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
        return $this->queryEvents(
            $this->eventService->list(array('resultsperpage' => $limit, 'start' => $start, 'filter' => $filter))
        );
    }

    /**
     * Look up this friendlyUrl in the DB, get an API endpoint, fetch data and return us an event.
     *
     * @param string $friendlyUrl The nice url bit of the event (e.g. phpbenelux-conference-2014)
     *
     * @return Event|false The event we found, or false if something went wrong
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
     * @return Event|false The event we found, or false if something went wrong
     */
    public function getByStub($stub)
    {
        return $this->fetchEventFromDbByProperty('stub', $stub);
    }

	/**
	 * Get comments for given event comment uri.
     *
	 * @param string $commentUri
	 * @param bool   $verbose
     *
	 * @return Event\Comment[]
	 */
    public function getComments($commentUri, $verbose = false)
    {
        /** @var Response $response */
        $response = $this->eventCommentService->list(array('url' => $commentUri));

        return $response->getResource();
    }

    public function addComment(Event $event, $comment)
    {
        try {
            $this->eventCommentService->submit(array('url' => $event->getCommentsUri(), 'comment' => $comment));
        } catch (\Exception $e) {
            throw new \Exception('Failed to add comment');
        }
    }

    public function attend(Event $event)
    {
        try {
            $this->eventService->getHttpClient()->post($event->getAttendingUri());
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark you as attending');
        }
    }

    /**
     * Submits a new event to the API.
     *
     * @param array $data
     *
     * @throws \Exception if a status code other than 201 is returned.
     *
     * @see EventFormType::buildForm() for a list of supported fields in the $data array and their constraints.
     *
     * @return Event
     */
    public function submit(array $data)
    {
        // convert timezone variable into appropriate sub-elements for api.
        if (isset($data['timezone'])) {
            list($tz_continent, $tz_place) = explode('/', $data['timezone']);
            unset($data['timezone']);
            $data['tz_continent'] = $tz_continent;
            $data['tz_place'] = $tz_place;
        }

        // Convert datetime objects to strings
        $data['start_date'] = $data['start_date'] instanceof \DateTime
            ? $data['start_date']->format('Y-m-d')
            : $data['start_date'];
        $data['end_date'] = $data['end_date'] instanceof \DateTime
            ? $data['end_date']->format('Y-m-d')
            : $data['end_date'];
        $data['cfp_start_date'] = $data['cfp_start_date'] instanceof \DateTime
            ? $data['cfp_start_date']->format('Y-m-d')
            : $data['cfp_start_date'];
        $data['cfp_end_date'] = $data['cfp_end_date'] instanceof \DateTime
            ? $data['cfp_end_date']->format('Y-m-d')
            : $data['cfp_end_date'];

        try {
            $response = $this->eventService->submit($data);
        } catch (\Exception $e) {
            throw new \Exception('Your event submission was not accepted, the server reports: ' . $e->getMessage());
        }

        $response = $this->queryEvents($response['location']);

        return current($response['events']);
    }

    /**
     * Returns a response array containing an 'events' and 'pagination' element.
     *
     * Each event in this response is also stored in the cache so that a relation can be made between the API URLs and
     * Event entities.
     *
     * @param Response $response
     *
     * @return array
     */
    private function queryEvents(Response $response)
    {
        /** @var Event[] $events */
        $events = $response->getResource();
        foreach ($events as $event) {
            $this->saveEventUrl($event);
        }

        $collectionData = array();
        $collectionData['events']     = $events;
        $collectionData['pagination'] = $response->getMeta();

        return $collectionData;
    }

    /**
     * Take an event and save the url_friendly_name and the API URL for that
     *
     * @param Event $event The event to take details from
     *
     * @return void
     */
    private function saveEventUrl(Event $event)
    {
        $this->eventDb->save($event);
    }

    /**
     * @param $propertyName
     * @param $propertyValue
     * @return bool|mixed
     */
    private function fetchEventFromDbByProperty($propertyName, $propertyValue)
    {
        $event = $this->eventDb->load($propertyName, $propertyValue);
        if (! $event) {
            return false;
        }

        /** @var Response $response */
        $response = $this->eventService->fetch(array('url' => $event['uri']));

        return current($response->getResource());
    }
}
