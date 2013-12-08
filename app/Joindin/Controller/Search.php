<?php
namespace Joindin\Controller;
/**
 * Class Search
 * SearchController that will be combining API calls to search for events and talks
 * or to search for both seperately
 *
 * @package Joindin\Controller
 */

class Search extends Base
{

    /**
     * @var integer The number of events / talks to fetch from the API
     */
    protected $limit;

    /**
     * Only one route for
     * @param \Slim $app
     */
    protected function defineRoutes(\Slim $app)
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

        if (!empty($keyword)) {
            $page = ((int)$this->application->request()->get('page') === 0)
                ? 1
                : $this->application->request()->get('page');

            $perPage = 10;
            $start = ($page -1) * $perPage;

            $event_collection = new \Joindin\Model\API\Search($this->accessToken);
            $events = $event_collection->getEventCollection($keyword, $perPage, $start);
            try {
                echo $this->application->render(
                    'Event/search.html.twig',
                    array(
                        'events'    => $events,
                        'page'      => $page,
                        'keyword'   => $keyword
                    )
                );
            } catch (\Twig_Error_Runtime $e) {
                $this->application->render(
                    'Error/app_load_error.html.twig',
                    array(
                        'message' => sprintf(
                            'An exception has been thrown during the rendering of '.
                            'a template ("%s").',
                            $e->getMessage()
                        ),
                        -1,
                        null,
                        $e
                    )
                );
            }
        }
    }
}
