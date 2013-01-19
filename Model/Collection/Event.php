<?php
namespace Joindin\Model\Collection;

class Event extends \Joindin\Model\API\JoindIn
{
    public function retrieve($limit = 10, $page = 1, $filter = null)
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

        return $events['events'];
    }

}
