<?php
namespace Event;

use Application\BaseApi;
use Talk\TalkCommentEntity;
use User\UserApi;

class EventApi extends BaseApi
{
    /**
     * @var EventDb
     */
    protected $eventDb;

    /**
     * @var UserApi
     */
    protected $userApi;

    public function __construct($config, $accessToken, EventDb $eventDb, UserApi $userApi)
    {
        parent::__construct($config, $accessToken);
        $this->eventDb = $eventDb;
        $this->userApi = $userApi;
    }

    /**
     * Get a paginated list of events, optionally applying a filter
     *
     * @param integer $limit       Number of events to get per page
     * @param integer $start       Start value for pagination
     * @param string  $filter      Filter to apply
     * @param bool    $verbose     get verbose result
     * @param array   $queryParams Additional query params as key => value pairs
     *
     * @return array
     */
    public function getEvents($limit = 10, $start = 1, $filter = null, $verbose = false, array $queryParams = [])
    {
        $url = $this->baseApiUrl . '/v2.1/events';
        $queryParams['resultsperpage'] = $limit;
        $queryParams['start'] = $start;

        if ($filter) {
            $queryParams['filter'] = $filter;
        }

        if ($verbose) {
            $queryParams['verbose'] = 'yes';
        }

        return $this->getCollection($url, $queryParams);
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
        $item = $this->eventDb->load('url_friendly_name', $friendlyUrl);

        if (!$item) {
            // don't throw an exception, Slim eats them
            return false;
        }

        return $this->getEvent($item['uri']);
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
        $item = $this->eventDb->load('stub', $stub);

        if (!$item) {
            return false;
        }

        return $this->getEvent($item['uri']);
    }

    /**
     * Gets event data from api on single talk
     *
     * @param string $event_uri  API talk uri
     * @param bool $verbose  Return verbose data?
     * @return TalkEntity
     */
    public function getEvent($event_uri, $verbose = true)
    {
        $params = array();
        if ($verbose) {
            $params['verbose'] = 'yes';
        }

        $event_list = (array)json_decode($this->apiGet($event_uri, $params));
        if (isset($event_list['events']) && isset($event_list['events'][0])) {
            $event = new EventEntity($event_list['events'][0]);
            $this->eventDb->save($event);
            return $event;
        }
        
        return false;
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
            $comment_uri = $comment_uri . '?verbose=yes&resultsperpage=0';
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
            $response = $this->getCollection($headers['location']);
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
     * Each event in this response is also stored in the cache so that a relation
     * can be made between the API URLs and
     * Event entities.
     *
     *
     * @param string $url API Url to query for one or more events. Either
     *                    a listing can be retrieved or a single event.
     * @param array  $queryParams
     *
     * @return array
     */
    public function getCollection($uri, array $queryParams = array())
    {
        $events = (array)json_decode($this->apiGet($uri, $queryParams));
        $meta   = array_pop($events);

        $collectionData = array();
        foreach ($events['events'] as $item) {
            $event = new EventEntity($item);
            $collectionData['events'][] = $event;

            // save the URL so we can look up by it
            $this->eventDb->save($event);
        }
        $collectionData['pagination'] = $meta;

        return $collectionData;
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

        foreach ($comments['comments'] as $item) {
            if (isset($item->user_uri)) {
                $item->username = $this->userApi->getUsername($item->user_uri);
            }
            $commentData['comments'][] = new TalkCommentEntity($item);
        }

        $commentData['pagination'] = $meta;

        return $commentData;
    }
}
