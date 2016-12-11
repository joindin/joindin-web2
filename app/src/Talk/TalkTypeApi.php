<?php
namespace Talk;

use Application\BaseApi;

class TalkTypeApi extends BaseApi
{
    /**
     * Retrieve list of talk types from the API
     *
     * @return array
     */
    public function getTalkTypes()
    {
        $url = $this->baseApiUrl . '/v2.1/talk_types';
        $queryParams['resultsperpage'] = 0;

        $result = $this->apiGet($url, $queryParams);
        if (!$result) {
            throw new \RuntimeException('Unable to retrieve list of talk types');
        }

        return json_decode($result, true);
    }

    /**
     * Return the list of talk types in a format suitable for a choice list
     *
     * @return array
     */
    public function getTalkTypesChoiceList()
    {
        $types = [];

        $list = $this->getTalkTypes();
        foreach ($list['talk_types'] as $type) {
            $types[$type['title']] = $type['title'];
        }

        return $types;
    }
}
