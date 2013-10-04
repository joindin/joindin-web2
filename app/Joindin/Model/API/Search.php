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
     *
     * @param int $limit
     * @param int $start
     * @param string $keyword
     * @param null $filter
     * @return array
     */
    public function getEventCollection($limit = 10, $start = 1, $keyword, $filter = null)
    {
        $url = $this->baseApiUrl . '/v2.1/events'
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

        $collectionData = array();
        foreach ($events['events'] as $event) {
            $collectionData['events'][] = new \Joindin\Model\Event($event);
        }
        $collectionData['pagination'] = $meta;

        return $collectionData;
    }


    /**
     *
     * @param int $limit
     * @param int $start
     * @param string $keyword
     * @param null $filter
     * @return array
     */
    public function getTalkCollection($limit = 10, $start = 1, $keyword, $filter = null)
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
