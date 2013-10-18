<?php
namespace Joindin\Model\API;

class Talk extends \Joindin\Model\API\JoindIn
{
    /**
     * Get all talks associated with an event
     *
     * @param $talks_uri  API talk uri
     *
     * @usage
     * $talkapi = new \Joindin\Model\API\Talk();
     * $talkapi->getCollection()
     *
     * @return \Joindin\Model\Talk model
     */
    public function getCollection($talks_uri)
    {
        $talks = (array)json_decode(
            $this->apiGet($talks_uri)
        );

        $collectionData = array();
        foreach ($talks['talks'] as $talk) {
            $collectionData['talks'][] = new \Joindin\Model\Talk($talk);
        }


        return $collectionData;
    }
}
