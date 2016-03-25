<?php
namespace Event;

use Application\BaseApi;

class TrackApi extends BaseApi
{
    public function __construct($config, $accessToken)
    {
        parent::__construct($config, $accessToken);
    }

    /**
     * Retrieve list of tracks from the API
     *
     * @return array
     */
    public function getTracks($url)
    {
        $queryParams['resultsperpage'] = 0;

        $result = $this->apiGet($url, $queryParams);
        if (!$result) {
            throw new \RuntimeException('Unable to retrieve list of tracks');
        }

        return json_decode($result, true);
    }

    /**
     * Return the list of tracks in a format suitable for a choice list
     *
     * @return array
     */
    public function getTracksChoiceList($url)
    {
        $tracks = [];

        $list = $this->getTracks($url);
        foreach ($list['tracks'] as $track) {
            $tracks[$track['track_name']] = $track['track_name'];
        }

        return $tracks;
    }
}
