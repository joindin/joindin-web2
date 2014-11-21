<?php
namespace Event;

use Application\BaseApi;
use Talk\TalkCommentEntity;

class EventApi extends BaseApi
{
    /**
     * @var EventDb
     */
    protected $eventDb;

    public function __construct($config, $accessToken, EventDb $eventDb)
    {
        parent::__construct($config, $accessToken);
        $this->eventDb = $eventDb;
    }

    /**
     * Get the latest events
     *
     * @param integer $limit  Number of events to get per page
     * @param integer $start  Start value for pagination
     * @param string  $filter Filter to apply
     * @param bool  $verbose get verbose result
     *
     * @return EventEntity model
     */
    public function getCollection($limit = 10, $start = 1, $filter = null, $verbose = false)
    {
        $url = $this->baseApiUrl . '/v2.1/events'
            . '?resultsperpage=' . $limit
            . '&start=' . $start;

        if ($filter) {
            $url .= '&filter=' . $filter;
        }

        if ($verbose) {
            $url .= '&verbose=yes';
        }

        return $this->queryEvents($url);
    }

    /**
     * Look up this friendlyUrl in the DB, get an API endpoint, fetch data
     * and return us an event
     *
     * @param string $friendlyUrl The nice url bit of the event (e.g. phpbenelux-conference-2014)
     * @return EventEntity The event we found, or false if something went wrong
     */
    public function getByFriendlyUrl($friendlyUrl)
    {
        $event = $this->eventDb->load('url_friendly_name', $friendlyUrl);

        if (!$event) {
            // don't throw an exception, Slim eats them
            return false;
        }

        $event_list = json_decode($this->apiGet($event['verbose_uri']));
        $event = new EventEntity($event_list->events[0]);

        return $event;

    }

    /**
     * Look up this stub in the DB, get an API endpoint, fetch data
     * and return us an event
     *
     * @param string $stub The short url bit of the event (e.g. phpbnl14)
     * @return EventEntity The event we found, or false if something went wrong
     */
    public function getByStub($stub)
    {
        $event = $this->eventDb->load('stub', $stub);

        if (!$event) {
            return false;
        }

        $event_list = json_decode($this->apiGet($event['verbose_uri']));
        $event = new EventEntity($event_list->events[0]);

        return $event;
    }

    /**
     * Get comments for given event
     * @param $comment_uri
     * @param bool $verbose
     * @return Comment[]
     */
    public function getComments($comment_uri, $verbose = false)
    {
        if ($verbose) {
            $comment_uri = $comment_uri . '?verbose=yes';
        }

        $comments = (array)json_decode($this->apiGet($comment_uri));

        $commentData = array();

        foreach ($comments['comments'] as $comment) {
            $commentData[] = new EventCommentEntity($comment);
        }

        return $commentData;
    }

    public function addComment($event, $comment)
    {
        $uri = $event->getCommentsUri();
        $params = array(
            'comment' => $comment,
        );
        list ($status, $result) = $this->apiPost($uri, $params);

        if ($status == 201) {
            return true;
        }
        throw new \Exception("Failed to add comment: " . $result);
    }

    public function attend(EventEntity $event)
    {
        list ($status, $result) = $this->apiPost($event->getApiUriToMarkAsAttending());

        if ($status == 201) {
            return true;
        }

        throw new \Exception("Failed to mark you as attending: " . $result);
    }

    public function unattend(EventEntity $event)
    {
        list ($status, $result) = $this->apiDelete($event->getApiUriToMarkAsAttending());

        if ($status == 200) {
            return true;
        }

        throw new \Exception("Failed to unmark you as attending: " . $result);
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
     * @return EventEntity|null
     */
    public function submit(array $data)
    {
        // Convert datetime objects to strings
        $dateFields = array('start_date', 'end_date', 'cfp_start_date', 'cfp_end_date');
        foreach ($dateFields as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof \DateTime) {
                $data[$dateField] = $data[$dateField]->format('Y-m-d');
            }
            if (isset($data[$dateField])) {
                if (!strtotime($data[$dateField])) {
                    unset($data[$dateField]);
                }
            }
        }
        // Convert comma-separated tags list into array
        $data['tags'] = array_map(function ($item) {
            return trim($item);
        }, explode(',', $data['tags']));

        list ($status, $result, $headers) = $this->apiPost($this->baseApiUrl . '/v2.1/events', $data);

        // if successful, return event entity represented by the URL in the Location header
        if ($status == 201) {
            $response = $this->queryEvents($headers['location']);
            return current($response['events']);
        }
        if ($status == 202) {
            return null;
        }

        throw new \Exception('Your event submission was not accepted, the server reports: ' . $result);
    }

    /**
     * Returns a response array containing an 'events' and 'pagination' element.
     *
     * Each event in this response is also stored in the cache so that a relation can be made between the API URLs and
     * Event entities.
     *
     * @param string $url API Url to query for one or more events. Either a listing can be retrieved or a single event.
     *
     * @return array
     */
    private function queryEvents($url)
    {
        $events = (array)json_decode($this->apiGet($url));
        $meta   = array_pop($events);

        $collectionData = array();
        foreach ($events['events'] as $event) {
            $thisEvent = new EventEntity($event);
            $collectionData['events'][] = $thisEvent;

            // save the URL so we can look up by it
            $this->saveEventUrl($thisEvent);
        }
        $collectionData['pagination'] = $meta;

        return $collectionData;
    }

    /**
     * Take an event and save the url_friendly_name and the API URL for that
     *
     * @param EventEntity $event The event to take details from
     *
     * @return void
     */
    private function saveEventUrl(EventEntity $event)
    {
        $this->eventDb->save($event);
    }

    /**
     * Get comments for all the talks of a given event
     *
     * @param string $comment_uri
     * @param int   $limit
     * @param int   $start
     * @param bool  $verbose
     *
     * @return array An array with two keys:
     *              'comments' holds the actual talk comment entities
     *              'pagination' holds pagination related meta data
     */
    public function getTalkComments($comment_uri, $limit = 10, $start = 1, $verbose = false)
    {
        $comment_uri .= '?resultsperpage=' . $limit
                      . '&start=' . $start;

        if ($verbose) {
            $comment_uri = $comment_uri . '&verbose=yes';
        }

        $comments = (array)json_decode($this->apiGet($comment_uri));

        $meta = array_pop($comments);

        $commentData = array();

        foreach ($comments['comments'] as $comment) {
            $commentData['comments'][] = new TalkCommentEntity($comment);
        }

        $commentData['pagination'] = $meta;

        return $commentData;
    }
}
