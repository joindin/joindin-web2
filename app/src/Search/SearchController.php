<?php
namespace Search;

use Application\BaseController;
use Application\CacheService;
use Event\EventApi;
use Event\EventDb;
use Talk\TalkApi;
use Talk\TalkDb;
use User\UserDb;
use User\UserApi;

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
     * @param \Slim $app
     */
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/search/events', array($this, 'searchEvents'))->name("search-events");
        $app->get('/search', array($this, 'search'))->name("search");
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

        if (!empty($keyword) || !empty($tag)) {
            $events = $this->searchEventsByTitleAndTag($page, $keyword, $tag);
        }

        $this->render(
            'Event/search.html.twig',
            array(
                'events'  => $events,
                'page'    => $page,
                'keyword' => $keyword,
                'tag'     => $tag
            )
        );
    }

    /**
     * Search both events and talks
     */
    public function search()
    {
        $keyword = $this->sanitizeKeyword($this->application->request()->get('keyword'));
        $events = array();
        $talks = array();
        $eventInfo = array();
        $pagination = array();

        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        if (!empty($keyword)) {
            $events = $this->searchEventsByTitleAndTag($page, $keyword);
            $talks = $this->searchTalksByTitle($page, $keyword);

            // combine pagination data for events and talks
            $pagination = $this->combinePaginationData(
                [$events['pagination'], $talks['pagination']]
            );
        }

        if (!empty($talks['talks'])) {
            $eventInfo = $this->getEventInfoForTalks($talks['talks']);
        }
        
        $this->render(
            'Application/search.html.twig',
            array(
                'events'     => $events,
                'eventInfo'  => $eventInfo,
                'talks'      => $talks,
                'page'       => $page,
                'pagination' => $pagination,
                'keyword'    => $keyword
            )
        );
    }

    /**
     * @param int    $page
     * @param string $keyword
     * @param string $tag
     *
     * @return array
     */
    private function searchEventsByTitleAndTag($page, $keyword, $tag = null)
    {
        $apiQueryParams = array();

        if (!empty($keyword)) {
            $apiQueryParams['title'] = $keyword;
        }

        if (!empty($tag)) {
            $apiQueryParams['tags'] = $tag;
        }

        $start = ($page - 1) * $this->itemsPerPage;

        return $this->getEventApi()->getEvents(
            $this->itemsPerPage,
            $start,
            null,
            false,
            $apiQueryParams
        );
    }

    /**
     * @param int    $page
     * @param string $keyword
     *
     * @return array
     */
    private function searchTalksByTitle($page, $keyword)
    {
        $apiQueryParams = [
            'title' => $keyword,
            'resultsperpage' => $this->itemsPerPage,
            'start' => ($page - 1) * $this->itemsPerPage
        ];

        return $this->getTalkApi()->getCollection(
            null, // pass empty $talks_uri so the base talks uri will be used
            $apiQueryParams
        );
    }

    /**
     * @return CacheService
     */
    private function getCache()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        return new CacheService($keyPrefix);
    }

    /**
     * @return EventApi
     */
    protected function getEventApi()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $eventDb = new EventDb($cache);
        $eventApi = new EventApi($this->cfg, $this->accessToken, $eventDb, $this->getUserApi());

        return $eventApi;
    }

    /**
     * @return TalkApi
     */
    protected function getTalkApi()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $talkDb = new TalkDb($cache);
        $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb, $this->getUserApi());

        return $talkApi;
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        $userDb = new UserDb($this->getCache());
        return new UserApi($this->cfg, $this->accessToken, $userDb);
    }

    /**
     * @param array $talks Array of talk entities
     *
     * @return array An array of event entities where event uri is the key
     */
    private function getEventInfoForTalks(array $talks)
    {
        $eventApi = $this->getEventApi();

        $events = [];
        foreach ($talks as $talk) {
            $eventUri = $talk->getEventUri();
            $events[$eventUri] = $eventApi->getEvent($eventUri);
        }

        return $events;
    }

    /**
     * @param array $paginations
     *
     * @return array
     */
    private function combinePaginationData(array $paginations)
    {
        $result = [
            'count' => 0,
            'total' => 0,
        ];

        foreach ($paginations as $pagination) {
            $result['count'] = max(
                $result['count'],
                $pagination->count
            );

            $result['total'] = max(
                $result['total'],
                $pagination->total
            );

            // prev_page & next_page only currently checked if they are defined,
            // so the contents does not really matter
            if (isset($pagination->prev_page)) {
                $result['prev_page'] = true;
            }

            if (isset($pagination->next_page)) {
                $result['next_page'] = true;
            }
        }

        return $result;
    }
}
