<?php
namespace Talk;

use Application\BaseApi;

class TalkApi extends BaseApi
{

    /**
     * @var TalkDb
     */
    protected $talkDb;

    /**
     * @param TalkDb $talkDb
     */
    public function __construct($config, $accessToken, TalkDb $talkDb)
    {
        parent::__construct($config, $accessToken);
        $this->talkDb = $talkDb;
    }


    /**
     * Get all talks associated with an event
     *
     * @param $talks_uri  API talk uri
     *
     * @return TalkEntity model
     */
    public function getCollection($talks_uri, $queryParams = array())
    {
        $talks = (array)json_decode(
            $this->apiGet($talks_uri, $queryParams)
        );

        $collectionData = array();
        foreach ($talks['talks'] as $item) {
            $talk = new TalkEntity($item);
            $collectionData['talks'][] = $talk;
            $this->talkDb->save($talk);
        }

        return $collectionData;
    }

    /**
     * Gets talk data from api on single talk
     *
     * @param string $talk_uri  API talk uri
     * @param bool $verbose  Return verbose data?
     * @return TalkEntity
     */
    public function getTalk($talk_uri, $verbose = false)
    {
        if ($verbose) {
            $talk_uri = $talk_uri . '?verbose=yes';
        }

        $collection = (array)json_decode($this->apiGet($talk_uri));

        $talk = new TalkEntity($collection['talks'][0]);
        $this->talkDb->save($talk);
        return $talk;
    }

    /**
     * Get Comments for given talk
     *
     * @param $comment_uri
     * @param bool $verbose
     * @return Comment[]
     */
    public function getComments($comment_uri, $verbose = false, $limitTo = null)
    {
        $params = [];
        if ($verbose) {
            $params['verbose'] = 'yes';
        }

        if ($limitTo) {
            $params['resultsperpage'] = $limitTo   ;
        }
        
        $comments = (array)json_decode($this->apiGet($comment_uri, $params));

        $commentData = array();

        foreach ($comments['comments'] as $comment) {
            $commentData[] = new TalkCommentEntity($comment);
        }

        return $commentData;
    }

    /**
     * Add a comment
     *
     * @param TalkEntity $talk
     * @param int $rating
     * @param string $comment
     */
    public function addComment($talk, $rating, $comment)
    {
        $uri = $talk->getCommentsUri();
        $params = array(
            'rating' => $rating,
            'comment' => $comment,
        );
        list ($status, $result) = $this->apiPost($uri, $params);

        if ($status == 201) {
            return true;
        }
        throw new \Exception("Failed to add comment: " . $result);
    }
}
