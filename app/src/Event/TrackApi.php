<?php
namespace Event;

use Application\BaseApi;

class TrackApi extends BaseApi
{
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
            $tracks[$track['uri']] = $track['track_name'];
        }

        return $tracks;
    }

    /**
     * Update a track
     *
     * @param  string $trackUri
     * @param  array $data
     */
    public function updateTrack($trackUri, $data)
    {
        $params = [
            'track_name'        => $data['track_name'],
            'track_description' => $data['track_description'] ?? '',
        ];

        list($status, $result, $headers) = $this->apiPut($trackUri, $params);
        if ($status == 204) {
            return true;
        }

        $result  = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed: " . $message);
    }

    /**
     * Add a track to an event's tracks collection
     *
     * @param  string $eventTracksUri
     * @param  array $data
     */
    public function addTrack($eventTracksUri, $data)
    {
        $params = [
            'track_name'        => $data['track_name'],
            'track_description' => $data['track_description'] ?? '',
        ];

        list($status, $result, $headers) = $this->apiPost($eventTracksUri, $params);
        if ($status == 201) {
            return true;
        }

        $result  = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed: " . $message);
    }

    /**
     * Delete a track
     *
     * @param string $trackUri
     */
    public function deleteTrack($trackUri)
    {
        list($status, $result, $headers) = $this->apiDelete($trackUri);
        if ($status == 204) {
            return true;
        }

        $result  = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed to delete track: " . $message);
    }
}
