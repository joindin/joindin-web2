<?php
namespace Joindin\Model\API;

use Joindin\Model\Comment;

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
     * @param bool $verbose  Return verbose data?
     * @return \Joindin\Model\Talk
     */
    public function getTalk($talk_uri, $verbose = false)
    {
        if($verbose) {
            $talk_uri = $talk_uri . '?verbose=yes';
        }

        $talk = (array)json_decode($this->apiGet($talk_uri));

        return new \Joindin\Model\Talk($talk['talks'][0]);
    }

    /**
     * Get Comments for given talk
     *
     * @param $comment_uri
     * @param bool $verbose
     * @return Comment[]
     */
    public function getComments($comment_uri, $verbose = false)
    {
        if($verbose) {
            $comment_uri = $comment_uri . '?verbose=yes';
        }

        $comments = (array)json_decode($this->apiGet($comment_uri));

        $commentData = array();

        foreach($comments['comments'] as $comment) {
            $commentData[] = new Comment($comment);
        }

        return $commentData;
    }
}
