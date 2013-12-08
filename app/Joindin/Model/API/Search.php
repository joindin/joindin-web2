<?php
namespace Joindin\Model\API;

/**
 * Class Search
 * Model to fetch tags or events form the API based on a search parameter (string)
 * @package Joindin\Model\API
 */
class Search extends \Joindin\Model\API\JoindIn
{
    /**
     * Calls API to search for events by title and returns a collection of events
     *
     * @param string $keyword
     * @param int $limit
     * @param int $start
     * @param null $filter
     * @return array
     */
    public function getEventCollection($keyword, $limit = 10, $start = 1, $filter = null)
    {
        $url = $this->baseApiUrl . '/v2.1/events'
            . '?resultsperpage=' . $limit
            . '&title=' . $keyword
            . '&start=' . $start;

        if ($filter) {
            $url .= '&filter=' . $filter;
        }

        $events = (array)json_decode(
            $this->apiGet($url)
        );

        $meta = array_pop($events);

        $collectionData = array();
        if($events['events']) {
            foreach ($events['events'] as $event) {
                $collectionData['events'][] = new \Joindin\Model\Event($event);
            }
            $collectionData['pagination'] = $meta;
        }

        return $collectionData;
    }


    /**
     * Calls API to search for talks by keyword (stub) and returns a collection of talks
     *
     * @param string $keyword
     * @param int $limit
     * @param int $start
     * @param null $filter
     * @return array
     */
    public function getTalkCollection($keyword, $limit = 10, $start = 1, $filter = null)
    {
        $url = $this->baseApiUrl . '/v2.1/talks'
            . '?resultsperpage=' . $limit
            . '&stub=' . $keyword
            . '&start=' . $start;

        if ($filter) {
            $url .= '&filter=' . $filter;
        }

        $events = (array)json_decode(
            $this->apiGet($url)
        );

        $meta = array_pop($events);
// todo make this actually work with talks!! Theres no talk model just yet
        $collectionData = array();
        foreach ($events['events'] as $event) {
            $collectionData['events'][] = new \Joindin\Model\Event($event);
        }
        $collectionData['pagination'] = $meta;

        return $collectionData;
    }


}
