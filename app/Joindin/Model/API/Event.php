<?php
namespace Joindin\Model\API;

class Event extends \Joindin\Model\API\JoindIn
{
    protected $mongoDatabaseName;

    public function __construct($configObj, $accessToken)
    {
        parent::__construct($configObj, $accessToken);

        $config = $configObj->getConfig();
        if (isset($config['mongo']) && isset($config['mongo']['database_name'])) {
            $this->mongoDatabaseName = $config['mongo']['database_name'];
        }

        $this->accessToken = $accessToken;
    }

    /**
     * Get the latest events
     *
     * @param $limit Number of events to get per page
     * @param $start Start value for pagination
     * @param $filter Filter to apply
     * @param $metaOnly Only return meta data?
     *
     * @usage
     * $eventapi = new \Joindin\Model\API\Event();
     * $eventapi->getCollection()
     *
     * @return \Joindin\Model\Event model
     */
    public function getCollection($limit = 10, $start = 1, $filter = null)
    {
        $url = $this->baseApiUrl . '/v2.1/events'
            . '?resultsperpage=' . $limit
            . '&start=' . $start;

        if ($filter) {
            $url .= '&filter=' . $filter;
        }

        $events = (array)json_decode(
            $this->apiGet($url)
        );

        $meta = array_pop($events);

        $collectionData = array();
        foreach ($events['events'] as $event) {
            $thisEvent = new \Joindin\Model\Event($event);
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
     * @param \Joindin\Model\Event $event The event to take details from
     */
    protected function saveEventUrl($event) {
        $db = new \Joindin\Service\Db($this->mongoDatabaseName);
    
        $data = array(
            "url_friendly_name" => $event->getUrlFriendlyName(),
            "uri" => $event->getUri(),
            "stub" => $event->getStub(),
            "verbose_uri" => $event->getVerboseUri()
        );

        // criteria for mongo to update or insert by
        $criteria = array(
            "uri" => $event->getUri()
        ); 

        $db->save('events', $data, $criteria);
    }

    /**
     * Look up this friendlyUrl in the DB, get an API endpoint, fetch data
     * and return us an event
     *
     * @param string $friendlyUrl The nice url bit of the event (e.g. phpbenelux-conference-2014)
     * @return \Joindin\Model\Event The event we found, or false if something went wrong
     */
    public function getByFriendlyUrl($friendlyUrl) {
        $db = new \Joindin\Service\Db($this->mongoDatabaseName);
        $event = $db->getOneByKey('events', 'url_friendly_name', $friendlyUrl);

        if (!$event) {
            // don't throw an exception, Slim eats them
            return false;
        }

        $event_list = json_decode($this->apiGet($event['verbose_uri']));
        $event = new \Joindin\Model\Event($event_list->events[0]);

        $data = json_decode($this->apiGet($event->getCommentsUri(), array("verbose" => "yes")));
        $event->setComments($data->comments);

        return $event;

    }

    /**
     * Look up this stub in the DB, get an API endpoint, fetch data
     * and return us an event
     *
     * @param string $stub The short url bit of the event (e.g. phpbnl14)
     * @return \Joindin\Model\Event The event we found, or false if something went wrong
     */
    public function getByStub($stub) {
        $db = new \Joindin\Service\Db($this->mongoDatabaseName);

        $event = $db->getOneByKey('events', 'stub', $stub);

        if (!$event) {
            return false;
        }

        $event_list = json_decode($this->apiGet($event['verbose_uri']));
        $event = new \Joindin\Model\Event($event_list->events[0]);

        $data = json_decode($this->apiGet($event->getCommentsUri()));
        $event->setComments($data->comments);

        return $event;
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
}
