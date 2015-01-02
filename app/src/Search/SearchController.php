<?php
namespace Search;

use Application\BaseController;
use Application\CacheService;
use Event\EventApi;
use Event\EventDb;

/**
 * Class SearchController
 * SearchController that will be combining API calls to search for events and talks
 * or to search for both seperately
 */
class SearchController extends BaseController
{

    /**
     * @var integer The number of events / talks to fetch from the API
     */
    protected $limit;

    /**
     * @var integer The number of search results to show per page
     */
    protected $itemsPerPage = 10;

    /**
     * Only one route for
     * @param \Slim $app
     */
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/search/events', array($this, 'searchEvents'))->name("search-events");
    }

    /**
     * Sanitize the search string - based on stub definition
     *
     * @param string $keyword
     * @return bool
     */
    protected function sanitizeKeyword($keyword)
    {
        return preg_replace("/[^A-Za-z0-9-_[:space:]]/", '', $keyword);
    }

    /**
     * Sanitize a tag
     *
     * @param string $tag
     * @return bool
     */
    protected function sanitizeTag($tag)
    {
        return preg_replace("/[^A-Za-z0-9]/", '', $tag);
    }

    /**
     * Searches events on a kewyord
     *
     * Will return a list of $limit events
     *
     */
    public function searchEvents()
    {

        $keyword = $this->sanitizeKeyword($this->application->request()->get('keyword'));
        $tag = $this->sanitizeTag($this->application->request()->get('tag'));
        $events = array();

        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $apiQueryParams = array();

        if (!empty($keyword)) {
            $apiQueryParams['title'] = $keyword;
        }

        if (!empty($tag)) {
            $apiQueryParams['tags'] = $tag;
        }

        if (!empty($apiQueryParams)) {
            $start = ($page -1) * $this->itemsPerPage;

            $eventApi = $this->getEventApi();
            $events = $eventApi->getEvents(
                $this->itemsPerPage,
                $start,
                null,
                false,
                $apiQueryParams
            );
        }

        $this->render(
            'Event/search.html.twig',
            array(
                'events'    => $events,
                'page'      => $page,
                'keyword'   => $keyword,
                'tag'       => $tag
            )
        );
    }

    /**
     * @return EventApi
     */
    protected function getEventApi()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $eventDb = new EventDb($cache);
        $eventApi = new EventApi($this->cfg, $this->accessToken, $eventDb);

        return $eventApi;
    }
}
