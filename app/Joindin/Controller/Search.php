<?php
namespace Joindin\Controller;
/**
 * Class Search
 *  Attempt at a search Cotroller that will be combining API calls to search for events and talks
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
        $app->get('/search/all', array($this, 'index'));
        $app->post('/search/events', array($this, 'searchevents'));
        $app->get('/search/talks', array($this, 'searchTalks'));
    }

    /**
     * Simple regex to validate the input
     *
     * @param string $keyword
     * @return bool
     */
    protected function validateKeyword($keyword)
    {
        return preg_match("{^[a-zA-Z0-9[:space:]-_]+$}", $keyword);
    }

    /**
     * Combines search for events and tasks
     * Get results from both services and return both in a seperate list
     */
    public function index()
    {
        $this->searchEvents();
    }

    /**
     * Calls API to search for a kewyord
     *
     * Will return a list of $limit events
     * @throws Exception
     */
    public function searchEvents()
    {

        $keyword = $this->application->request()->post('keyword');
        if (!$this->validateKeyword($keyword)) {
            throw new \Exception('The keyword for the search was not valid!');
        }
        // TODO in stead of throwing an exception, tell the user

        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 10;
        $start = ($page -1) * $perPage;

        $event_collection = new \Joindin\Model\API\Search();
        $events = $event_collection->getEventCollection($perPage, $start, $keyword);
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

    public function searchTalks($keyword)
    {

    }


}
