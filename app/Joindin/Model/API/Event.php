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
            $collectionData['events'][] = new \Joindin\Model\Event($event);
        }
        $collectionData['pagination'] = $meta;

        return $collectionData;
    }


    /*
     * Get a single event, by slug
     *
     * @param $slug String of event to get
     * @usage
     * $eventapi = new \Joindin\Model\API\Event();
     * $eventapi->getBySlug('openwest-conference-2013')
     */

    public function getBySlug($slug)
    {
        $db = new \Joindin\Service\Db;
        $event = $db->getOneByKey('events', 'slug', $slug);

        // Throw exception if event not found
        if (!$event) {
            throw new \Exception('Event not found');
        }

        $event_list = json_decode($this->apiGet($event['verboseuri']));

        $event = new \Joindin\Model\Event($event_list->events[0]);

        $data = json_decode($this->apiGet($event->getCommentsUri(), array('verbose'=>'yes')), true);
        $event->setComments($data['comments']);

        // For later use, so that we don't have to
        $event->slug = $slug;
        $event->setSlug($slug);

        return $event;

    }

    public function addComment($comment)
    {
        $url = $this->getUri() . '/comments';
        $params = array(
            'comment' => $comment,
        );
        $result = $this->apiPost($url, $params);
        return $result;
    }

}
