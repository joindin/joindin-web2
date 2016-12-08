<?php
namespace Client;

use Application\BaseApi;
use User\UserApi;

class ClientApi extends BaseApi
{
    /**
     * Get all clients associated with the current user
     *
     * @return array
     */
    public function getCollection($queryParams)
    {
        $talks_uri = $this->baseApiUrl . '/v2.1/applications';

        $talks = (array) json_decode(
            $this->apiGet($talks_uri, $queryParams)
        );
        $meta  = array_pop($talks);

        $collectionData = array();
        foreach ($talks['clients'] as $item) {
            $talk = new ClientEntity($item);

            $collectionData['clients'][] = $talk;
        }

        $collectionData['pagination'] = $meta;

        return $collectionData;
    }

    /**
     * Get a specified client associated with the current user
     *
     * @return array
     */
    public function getById($id, $queryParams = ['verbose' => 'yes'])
    {
        $clients_uri = $this->baseApiUrl . '/v2.1/applications/' . urlencode($id);

        $clients = (array) json_decode(
            $this->apiGet($clients_uri, $queryParams)
        );

        return new ClientEntity($clients['clients'][0]);
    }


}
