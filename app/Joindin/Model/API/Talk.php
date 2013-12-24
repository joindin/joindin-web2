<?php
namespace Joindin\Model\API;

class Talk extends \Joindin\Model\API\JoindIn
{

    /**
     * @var \Joindin\Model\Db\Talk
     */
    protected $talkDb;

    /**
     * @param \Joindin\Model\Db\Talk $talkDb
     */
    function __construct($accessToken, \Joindin\Model\Db\Talk $talkDb)
    {
        parent::__construct($accessToken);
        $this->talkDb = $talkDb;
    }


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
            $talkObject = new \Joindin\Model\Talk($talk);
            $collectionData['talks'][] = $talkObject;
            $this->talkDb->saveSlugToDatabase($talkObject);
        }

        return $collectionData;
    }

    /**
     * Gets talk data from api on single talk
     *
     * @param string $talk_uri  API talk uri
     * @return \Joindin\Model\Talk
     */
    public function getTalk($talk_uri)
    {
        $talk = (array)json_decode($this->apiGet($talk_uri));
        return new \Joindin\Model\Talk($talk);
    }
}
