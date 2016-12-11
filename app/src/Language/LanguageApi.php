<?php
namespace Language;

use Application\BaseApi;

class LanguageApi extends BaseApi
{
    /**
     * Retrieve list of languages from the API
     *
     * @return array
     */
    public function getLanguages()
    {
        $url = $this->baseApiUrl . '/v2.1/languages';
        $queryParams['resultsperpage'] = 0;

        $result = $this->apiGet($url, $queryParams);
        if (!$result) {
            throw new \RuntimeException('Unable to retrieve list of languages');
        }

        return json_decode($result, true);
    }

    /**
     * Return the list of languages in a format suitable for a choice list
     *
     * @return array
     */
    public function getLanguagesChoiceList()
    {
        $languages = [];

        $list = $this->getLanguages();
        foreach ($list['languages'] as $language) {
            $languages[$language['name']] = $language['name'];
        }

        return $languages;
    }
}
