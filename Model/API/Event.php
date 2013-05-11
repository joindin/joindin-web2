<?php
namespace Joindin\Model\API;

class Event extends \Joindin\Model\API\JoindIn
{
    /**
     * Get the latest events
     *
     * @param $limit Number of events to get per page
     * @param $page Which page to get
     * @param $filter Filter to apply
     *
     * @usage
     * $eventapi = new \Joindin\Model\API\Event();
     * $eventapi->getCollection()
     *
     * @returns \Joindin\Model\Event model
     */
    public function getCollection($limit = 10, $page = 1, $filter = null)
    {
        $url = $this->baseApiUrl.'/v2.1/events'
            .'?resultsperpage='.$limit
            .'&page='.$page;
        if ($filter) {
            $url .= '&filter='.$filter;
        }

        $events = (array)json_decode(
            $this->apiGet($url)
        );
        $meta = array_pop($events);

        $collectionData = array();
        foreach ($events['events'] as $event) {
            $collectionData[] = new \Joindin\Model\Event($event);
        }

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

        $event = current(
            current(
                (array)json_decode(
                    $this->apiGet(
                        $this->baseApiUrl
                        .'/v2.1/events/'.$event['id'].'?format=json&verbose=yes'
                    )
                )
            )
        );

        $event->comments = current(
            (array)json_decode(
                $this->apiGet($event->comments_uri)
            )
        );

        // For later use, so that we don't have to
        $event->slug = $slug;

        return $event;

        // import properties
        /*foreach ($event as $key => $value) {
            $this->$key = $value;
        }*/
    }
}
