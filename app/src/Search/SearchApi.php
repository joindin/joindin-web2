<?php
namespace Search;

use Application\BaseApi;
use Event\EventEntity;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Joindin\Api\Response;

/**
 * Class SearchApi
 * Model to fetch tags or events form the API based on a search parameter (string)
 */
class SearchApi extends BaseApi
{
    /** @var GuzzleClient */
    private $eventService;

    public function __construct($config, $accessToken, GuzzleClient $eventService)
    {
        parent::__construct($config, $accessToken);

        $this->eventService = $eventService;
    }

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
        $params = array(
            'resultsperpage' => $limit,
            'title' => $keyword,
            'start' => $start
        );
        if ($filter) {
            $params['filter'] = $filter;
        }

        /** @var Response $response */
        $response = $this->eventService->list($params);

        $collectionData = array();
        foreach ($response->getResource() as $event) {
            $collectionData['events'][] = $event;
        }
        $collectionData['pagination'] = $response->getMeta();

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
            $collectionData['events'][] = new EventEntity($event);
        }
        $collectionData['pagination'] = $meta;

        return $collectionData;
    }


}
