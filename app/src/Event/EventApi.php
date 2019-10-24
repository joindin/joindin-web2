<?php
namespace Event;

use Application\BaseApi;
use DateTime;
use Exception;
use Talk\TalkCommentEntity;
use Talk\TalkCommentReportEntity;
use User\UserApi;
use User\UserEntity;

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
        $url                           = $this->baseApiUrl . '/v2.1/events';
        $queryParams['resultsperpage'] = $limit;
        $queryParams['start']          = $start;

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
     * @return EventEntity|false The event we found, or false if something went wrong
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
     * @return EventEntity|false The event we found, or false if something went wrong
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
     * @return EventEntity|bool
     */
    public function getEvent($event_uri, $verbose = true)
    {
        $params = [];
        if ($verbose) {
            $params['verbose'] = 'yes';
        }

        $event_list = (array)json_decode($this->apiGet($event_uri, $params));
        if (isset($event_list['events']) && isset($event_list['events'][0])) {
            $event = new EventEntity($event_list['events'][0]);
            $this->eventDb->save($event);

            foreach ($event->getHosts() as $hostsInfo) {
                if (isset($hostsInfo->host_uri)) {
                    $hostsInfo->username = $this->userApi->getUsername($hostsInfo->host_uri);
                }
            }
            return $event;
        }

        return false;
    }

    /**
     * Get an event by id
     *
     * @param integer $eventId
     * @return EventEntity|null
     */
    public function getEventById($eventId): ?EventEntity
    {
        $eventId = (int)$eventId;
        if (!$eventId) {
            return null;
        }

        $eventUrl = $this->baseApiUrl . '/v2.1/events/' . $eventId;

        return $this->getEvent($eventUrl);
    }

    /**
     * Get comments for given event
     * @param string $comment_uri
     * @param bool $verbose
     * @return EventCommentEntity[]
     */
    public function getComments($comment_uri, $verbose = false)
    {
        if ($verbose) {
            $comment_uri = $comment_uri . '?verbose=yes&resultsperpage=0';
        }

        $comments = (array)json_decode($this->apiGet($comment_uri));

        $commentData = [];

        foreach ($comments['comments'] as $comment) {
            $commentData[] = new EventCommentEntity($comment);
        }

        return $commentData;
    }

    public function addComment($event, $comment, $rating = 0)
    {
        $uri    = $event->getCommentsUri();
        $params = [
            'comment' => $comment,
            'rating'  => $rating,
        ];
        list($status, $result) = $this->apiPost($uri, $params);

        if ($status == 201) {
            return true;
        }
        throw new Exception("Failed to add comment: " . $result);
    }

    public function reportComment($uri)
    {
        list($status, $result) = $this->apiPost($uri);

        if ($status == 202) {
            return true;
        }
        throw new Exception("Failed to report comment: " . $result);
    }

    public function attend(EventEntity $event)
    {
        list($status, $result) = $this->apiPost($event->getApiUriToMarkAsAttending());

        if ($status == 201) {
            return true;
        }

        throw new Exception("Failed to mark you as attending: " . $result);
    }

    public function unattend(EventEntity $event)
    {
        list($status, $result) = $this->apiDelete($event->getApiUriToMarkAsAttending());

        if ($status == 200) {
            return true;
        }

        throw new Exception("Failed to unmark you as attending: " . $result);
    }


    /**
     * Get attendees for given event
     * @param string $attendees_uri
     * @param int $limit
     * @param bool $verbose
     * @return UserEntity[]
     */
    public function getAttendees($attendees_uri, $limit = 0, $verbose = false)
    {
        $attendees_uri .= "?resultsperpage={$limit}";
        if ($verbose) {
            $attendees_uri = $attendees_uri . '&verbose=yes';
        }


        $attendees = (array)json_decode($this->apiGet($attendees_uri));

        $attendeeData = [];

        foreach ($attendees['users'] as $attendee) {
            $attendeeData[] = new UserEntity($attendee);
        }

        return $attendeeData;
    }

    /**
     * Submits a new event to the API and returns it or null if it is pending acceptance.
     *
     * @param array $data
     *
     * @throws Exception if a status code other than 201 is returned.
     *
     * @see EventFormType::buildForm() for a list of supported fields in the $data array
     * and their constraints.
     *
     * @return EventEntity|null
     */
    public function submit(array $data)
    {
        // Convert datetime objects to strings
        $dateFields = ['start_date', 'end_date', 'cfp_start_date', 'cfp_end_date'];
        foreach ($dateFields as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof DateTime) {
                $data[$dateField] = $data[$dateField]->format('Y-m-d');
            }
            if (isset($data[$dateField])) {
                if (!strtotime($data[$dateField])) {
                    unset($data[$dateField]);
                }
            }
        }

        list($status, $result, $headers) = $this->apiPost($this->baseApiUrl . '/v2.1/events', $data);

        // if successful, return event entity represented by the URL in the Location header
        if ($status == 201) {
            $response = $this->getCollection($headers['location']);
            return current($response['events']);
        }
        if ($status == 202) {
            return null;
        }
        if ($status == 400) {
            $decoded = json_decode($result);
            if (is_array($decoded)) {
                $result = current($decoded);
            }
        }

        throw new Exception($result);
    }

    /**
     * Submit an edited event to the API and return it.
     *
     * If something happened NULL is returned
     *
     * @param array $data
     *
     * @throws Exception if a status code other than 201 is returned.
     * @see EventFormType::buildForm() for a list of supported fields in the $data array
     * @return EventEntity|null
     */
    public function edit(array $data)
    {
        // Convert datetime objects to strings
        $dateFields = ['start_date', 'end_date', 'cfp_start_date', 'cfp_end_date'];
        foreach ($dateFields as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof DateTime) {
                $data[$dateField] = $data[$dateField]->format('c');
            }
            if (isset($data[$dateField])) {
                if (!strtotime($data[$dateField])) {
                    unset($data[$dateField]);
                }
            }
        }


        list($status, $result, $headers) = $this->apiPut($data['uri'], $data);
        // if successful, return event entity represented by the URL in the Location header
        if ($status == 204) {
            $response = $this->getCollection($headers['location']);
            return current($response['events']);
        }

        throw new Exception('Your event submission was not accepted, the server reports: ' . $result);
    }

    /**
     * Upload event image - this one's a bit special as it's a form post
     *
     * Uses Guzzle
     *
     * @param  string $imagesUri event's images_uri
     * @param  string $fileName  the (temp) file to send
     * @return boolean
     */
    public function uploadIcon($imagesUri, $fileName)
    {
        try {
            $client = new \GuzzleHttp\Client([
                "timeout" => 10,
            ]);

            $headers                  = [];
            $headers["Accept"]        = "application/json";
            $headers["Authorization"] = "OAuth {$this->accessToken}";

            // Forwarded header - see RFC 7239 (http://tools.ietf.org/html/rfc7239)
            $ip                   = $_SERVER['REMOTE_ADDR'];
            $agent                = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
            $headers["Forwarded"] = "for=$ip;user-agent=\"$agent\"";

            $options            = [];
            $options['headers'] = $headers;

            if ($this->proxy) {
                $options['proxy'] = $this->proxy;
            }

            // now add the file itself
            $options['multipart'] = [['name' => 'image',
                'contents'                   => fopen($fileName, 'r')]];

            $request  = new \GuzzleHttp\Psr7\Request('POST', $imagesUri);
            $response = $client->send($request, $options);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody();
            error_log($e->getMessage());
            error_log(json_decode($body)[0]);
            throw new Exception(json_decode($body)[0]);
        }

        if ($response->getStatusCode() == 201) {
            return true;
        }

        throw new Exception((string)$response->getBody());
    }

    /**
     * Returns a response array containing an 'events' and 'pagination' element.
     *
     * Each event in this response is also stored in the cache so that a relation
     * can be made between the API URLs and Event entities.
     *
     * @param string $uri API Url to query for one or more events. Either a
     *                    listing can be retrieved or a single event.
     * @param array  $queryParams
     *
     * @return array
     */
    public function getCollection($uri, array $queryParams = [])
    {
        $events = (array)json_decode($this->apiGet($uri, $queryParams));
        $meta   = array_pop($events);

        $collectionData = [];
        foreach ($events['events'] as $item) {
            $event = new EventEntity($item);

            foreach ($event->getHosts() as $hostsInfo) {
                if (isset($hostsInfo->host_uri)) {
                    $hostsInfo->username = $this->userApi->getUsername($hostsInfo->host_uri);
                }
            }

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

        $commentData = [];

        foreach ($comments['comments'] as $item) {
            $commentData['comments'][] = new TalkCommentEntity($item);
        }

        $commentData['pagination'] = $meta;

        return $commentData;
    }

    /**
     * Approve a pending event by POSTing to approval_uri
     *
     * @param  string $approval_uri
     * @return boolean
     */
    public function approveEvent($approval_uri)
    {
        list($status, $result, $headers) = $this->apiPost($approval_uri);

        if ($status == 204) {
            return true;
        }
        throw new Exception("Failed to approve event: " . $result);
    }

    /**
     * Reject a pending event by DELETEing to approval_uri
     *
     * @param  string $approval_uri
     * @return boolean
     */
    public function rejectEvent($approval_uri)
    {
        list($status, $result, $headers) = $this->apiDelete($approval_uri);

        if ($status == 204) {
            return true;
        }
        throw new Exception("Failed to reject event: " . $result);
    }

    public function getReportedEventComments($comment_uri)
    {
        $response = json_decode($this->apiGet($comment_uri));

        $reports = [];

        foreach ($response->reports as $item) {
            $reports[] = new EventCommentReportEntity($item);
        }

        return $reports;
    }

    public function getPendingClaims($claims_uri, $verbose = false)
    {
        if ($verbose) {
            $claims_uri = $claims_uri . "?verbose=yes";
        }
        $response = json_decode($this->apiGet($claims_uri));

        $reports = [];

        foreach ($response->claims as $item) {
            $reports[] = $item;
        }

        return $reports;
    }

    public function getReportedTalkComments($comment_uri)
    {
        $response = json_decode($this->apiGet($comment_uri));

        $reports = [];

        foreach ($response->reports as $item) {
            $reports[] = new TalkCommentReportEntity($item);
        }

        return $reports;
    }

    /**
     * Moderate a comment by PUT'ing a decision to the reported_uri.
     *
     * @param string $reported_uri
     * @param string $decision
     * @throws Exception on error
     *
     * @return boolean
     */
    public function moderateComment($reported_uri, $decision)
    {
        $data['decision'] = $decision;

        list($status, $result, $headers) = $this->apiPut($reported_uri, $data);

        // if successful, return event entity represented by the URL in the Location header
        if ($status == 204) {
            return true;
        }

        throw new Exception('Your comment moderation was not accepted, the server reports: ' . $result);
    }
}
