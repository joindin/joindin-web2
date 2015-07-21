<?php
namespace Talk;

use Application\BaseApi;
use User\UserApi;

class TalkApi extends BaseApi
{
    /**
     * @var TalkDb
     */
    protected $talkDb;

    /**
     * @var UserApi
     */
    protected $userApi;

    /**
     * @param TalkDb $talkDb
     */
    public function __construct($config, $accessToken, TalkDb $talkDb, UserApi $userApi)
    {
        parent::__construct($config, $accessToken);
        $this->talkDb = $talkDb;
        $this->userApi = $userApi;
    }

    /**
     * Get all talks associated with an event
     *
     * @param string $talks_uri   API talk uri
     * @param array  $queryParams
     *
     * @return array
     */
    public function getCollection($talks_uri, array $queryParams = [])
    {
        if (empty($talks_uri)) {
            $talks_uri = $this->baseApiUrl . '/v2.1/talks';
        }

        $talks = (array)json_decode(
            $this->apiGet($talks_uri, $queryParams)
        );
        $meta = array_pop($talks);

        $collectionData = array();
        foreach ($talks['talks'] as $item) {
            $talk = new TalkEntity($item);

            foreach ($talk->getSpeakers() as $speakerInfo) {
                if (isset($speakerInfo->speaker_uri)) {
                    $speakerInfo->username = $this->userApi->getUsername($speakerInfo->speaker_uri);
                }
            }

            $collectionData['talks'][] = $talk;
            $this->talkDb->save($talk);
        }

        $collectionData['pagination'] = $meta;

        return $collectionData;
    }

    /**
     * Gets a talk when we know the slug and event's uri.
     *
     * @param  string $talkSlug
     * @param  string $eventUri
     * @return TalkEntity
     */
    public function getTalkBySlug($talkSlug, $eventUri)
    {
        $talkUri = $this->talkDb->getUriFor($talkSlug, $eventUri);
        if (!$talkUri) {
            return false;
        }

        return $this->getTalk($talkUri, true);
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

        foreach ($talk->getSpeakers() as $speakerInfo) {
            if (isset($speakerInfo->speaker_uri)) {
                $speakerInfo->username = $this->userApi->getUsername($speakerInfo->speaker_uri);
            }
        }

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

        if (null !== $limitTo) {
            $params['resultsperpage'] = $limitTo   ;
        }
        
        $comments = (array)json_decode($this->apiGet($comment_uri, $params));

        $commentData = array();

        foreach ($comments['comments'] as $item) {
            if (isset($item->user_uri)) {
                $item->username = $this->userApi->getUsername($item->user_uri);
            }

            $commentData[] = new TalkCommentEntity($item);
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

    /**
     * Star or unstar based on current setting of starred
     *
     * @param  TalkEntity $talk
     */
    public function toggleStar($talk)
    {
        if ($talk->getStarred()) {
            list ($status, $result) = $this->apiDelete($talk->getStarredUri(), []);
            if ($status == 200) {
                return ['starred' => false];
            }

        } else {
            list ($status, $result) = $this->apiPost($talk->getStarredUri(), []);
            if ($status == 201) {
                return ['starred' => true];
            }
        }
    
        throw new \Exception("Failed to toggle star: $status, $result");
    }

    public function getEmptyTalk()
    {

    }


    /**
     * Submit an edited talk to the API and return it.
     *
     * If the talk has been successfully stored TRUE is returned whereas if
     * something happened an Exception is thrown,
     *
     * @param array $date
     *
     * @throws \Exception if a status code other than 201 is returned.
     * @see TalkFormType::buildForm() for a list of supported fields in the $data array
     * @return boolean
     */
    public function edit(array $data)
    {
        // Convert datetime objects to strings
        $dateFields = array('start_date');
        foreach ($dateFields as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof \DateTime) {
                $data[$dateField] = $data[$dateField]->format('c');
            }
            if (isset($data[$dateField])) {
                if (!strtotime($data[$dateField])) {
                    unset($data[$dateField]);
                }
            }
        }

        list ($status, $result, $headers) = $this->apiPut($data['uri'], $data);
        // if successful, return event entity represented by the URL in the Location header
        if ($status == 204) {
            return true;
        }

        throw new \Exception('Your talk submission was not accepted, the server reports: ' . $result);
    }

    /**
     * Submits a new talk to the API and returns it or null if it is pending acceptance.
     *
     * @param array $data
     *
     * @throws \Exception if a status code other than 201 is returned.
     *
     * @see TalkFormType::buildForm() for a list of supported fields in the $data array
     * and their constraints.
     *
     * @return TalkEntity|null
     */
    public function submit(array $data, $talksUri)
    {
        // Convert datetime objects to strings
        $dateFields = array('start_date');
        foreach ($dateFields as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] instanceof \DateTime) {
                $data[$dateField] = $data[$dateField]->format('Y-m-d H:i:s');
            }
            if (isset($data[$dateField])) {
                if (!strtotime($data[$dateField])) {
                    unset($data[$dateField]);
                }
            }
        }

        list ($status, $result, $headers) = $this->apiPost($talksUri, $data);
        // if successful, return event entity represented by the URL in the Location header
        if ($status == 201) {
            $response = $this->getTalk($headers['location']);

            return $response;
        }
        if ($status == 202) {
            return null;
        }

        throw new \Exception('Your talk submission was not accepted, the server reports: ' . $result);
    }
}
