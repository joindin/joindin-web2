<?php
namespace Apikey;

use Application\BaseApi;

class ApikeyApi extends BaseApi
{
    /**
     * Get all tokens associated with the current user
     *
     * @return array
     */
    public function getCollection($queryParams)
    {
        $token_uri = $this->baseApiUrl . '/v2.1/token';

        $tokens = (array) json_decode(
            $this->apiGet($token_uri, $queryParams)
        );
        $meta  = array_pop($tokens);

        $collectionData = array();
        foreach ($tokens['tokens'] as $item) {
            $talk = new ApikeyEntity($item);

            $collectionData['tokens'][] = $talk;
        }

        $collectionData['pagination'] = $meta;

        return $collectionData;
    }

    /**
     * Get a specified API-key associated with the current user
     *
     * @return ApikeyEntity
     */
    public function getById($id, $queryParams = ['verbose' => 'yes'])
    {
        $tokens_uri = $this->baseApiUrl . '/v2.1/token/' . urlencode($id);

        $tokens = (array) json_decode(
            $this->apiGet($tokens_uri, $queryParams)
        );


        if (! isset($tokens['tokens'][0])) {
            throw new \UnexpectedValueException('No tokens available');
        }

        return new ApikeyEntity($tokens['tokens'][0]);
    }

    /**
     * @param $tokenUri
     *
     * @throws \Exception
     * @return bool
     */
    public function deleteClient($tokenUri)
    {
        list ($status, $result, $headers) = $this->apiDelete($tokenUri);

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
