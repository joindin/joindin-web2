<?php
namespace Search;

use Application\BaseController;
use Application\CacheService;
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
     * Only one route for
     * @param \Slim $app
     */
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/search/events', array($this, 'searchEvents'));
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
     * Searches events on a kewyord
     *
     * Will return a list of $limit events
     *
     */
    public function searchEvents()
    {

        $keyword = $this->sanitizeKeyword($this->application->request()->get('keyword'));
        $events = array();

        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        if (!empty($keyword)) {
            $perPage = 10;
            $start = ($page -1) * $perPage;

            $event_collection = new SearchApi($this->cfg, $this->accessToken);
            $events = $event_collection->getEventCollection($keyword, $perPage, $start);

            // Save to our data store
            $cache = new CacheService($this->cfg['redisKeyPrefix']);
            $eventDb = new EventDb($cache);
            foreach ($events['events'] as $event) {
                $eventDb->save($event);
            }
        }

        $this->render(
            'Event/search.html.twig',
            array(
                'events'    => $events,
                'page'      => $page,
                'keyword'   => $keyword
            )
        );
    }
}
