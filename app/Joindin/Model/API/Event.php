<?php
namespace Joindin\Model\API;

class Event extends \Joindin\Model\API\JoindIn
{
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
        $db = new \Joindin\Service\Db;
    
        $data = array(
            "url_friendly_name" => $event->getUrlFriendlyName(),
            "uri" => $event->getUri(),
            "verbose_uri" => $event->getVerboseUri()
        );

        $db->save('events', $data);
    }

}
