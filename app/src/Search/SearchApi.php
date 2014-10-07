<?php
namespace Search;

use Application\BaseApi;
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
    }

    /**
     * Calls API to search for events by title and returns a collection of events
     *
     * @param string $keyword
     * @param int $limit
     * @param int $start
     * @param null $filter
     *
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
}
