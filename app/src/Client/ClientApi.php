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

        $collectionData = ['clients' => []];
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
     * @return ClientEntity
     */
    public function getById($id, $queryParams = ['verbose' => 'yes'])
    {
        $clients_uri = $this->baseApiUrl . '/v2.1/applications/' . urlencode($id);

        $clients = (array) json_decode(
            $this->apiGet($clients_uri, $queryParams)
        );

        if (! isset($clients['clients'][0])) {
            throw new \UnexpectedValueException('No clients available');
        }

        return new ClientEntity($clients['clients'][0]);
    }

    /**
     * Submits a new client to the API and returns it
     *
     * @param array $data
     *
     * @throws \Exception if a status code other than 201 is returned.
     * @see ClientFormType::buildForm() for a list of supported fields in the $data array
     * and their constraints.
     * @return ClientEntity
     */
    public function submit(array $data)
    {
        $values = [
            'name'         => $data['application'],
            'description'  => $data['description'],
            'callback_url' => $data['callback_url'],
        ];
        list($status, $result, $headers) = $this->apiPost($this->baseApiUrl . '/v2.1/applications', $values);

        if ($status != 201) {
            $decoded = json_decode($result);
            if (is_array($decoded)) {
                $result = current($decoded);
            }
            throw new \Exception($result);
        }

        return $this->getById(substr($headers['location'], strrpos($headers['location'], '/') + 1));
    }

    /**
     * Submits data to edit an existing client to the API and returns it
     *
     * @param string $clientUri The API-URI for the client
     * @param array  $data
     *
     * @throws \Exception if a status code other than 201 is returned.
     * @see ClientFormType::buildForm() for a list of supported fields in the $data array
     * and their constraints.
     * @return ClientEntity
     */
    public function editClient($clientUri, array $data)
    {
        $values = [
            'name'         => $data['application'],
            'description'  => $data['description'],
            'callback_url' => $data['callback_url'],
        ];
        list($status, $result, $headers) = $this->apiPut($clientUri, $values);

        if ($status != 201) {
            $decoded = json_decode($result);
            if (is_array($decoded)) {
                $result = current($decoded);
            }
            throw new \Exception($result);
        }

        return $this->getById(substr($headers['location'], strrpos($headers['location'], '/') + 1));
    }

    /**
     * @param string $clientUri
     *
     * @throws \Exception
     * @return bool
     */
    public function deleteClient($clientUri)
    {
        list($status, $result, $headers) = $this->apiDelete($clientUri);

        if ($status != 204) {
            $decoded = json_decode($result);
            if (is_array($decoded)) {
                $result = current($decoded);
            }
            throw new \Exception($result);
        }

        return true;
    }
}
