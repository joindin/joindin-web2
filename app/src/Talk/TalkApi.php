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
     * @param integer $talkId
     * @return TalkEntity
     */
    public function getTalkByTalkId($talkId)
    {
        $talkId = (int)$talkId;
        if (!$talkId) {
            return;
        }

        $talkUrl = $this->baseApiUrl . '/v2.1/talks/' . $talkId;

        return $this->getTalk($talkUrl, true);
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

        if (!isset($collection['talks'])) {
            return false;
        }
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

    public function reportComment($uri)
    {
        list ($status, $result) = $this->apiPost($uri);

        if ($status == 202) {
            return true;
        }
        throw new \Exception("Failed to report comment: " . $result);
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

    /**
     * Retreive a list of talks organised by date and time
     *
     * @param  string $talksUri
     * @return array
     */
    public function getAgenda($talksUri)
    {
        $talks = $this->getCollection($talksUri . '?start=0&resultsperpage=1000&verbose=yes');
        if (!array_key_exists('talks', $talks)) {
            return [];
        }
        $talks = $talks['talks'];

        $agenda = [];

        foreach ($talks as $talk) {
            $date = $talk->getStartDateTime()->format("Y-m-d");
            $startTime = $talk->getStartDateTime()->format("H:i");
            $time = "$startTime";
            $agenda[$date][$time][] = $talk;
        }

        return $agenda;
    }

    /**
     * Add a talk to an event
     *
     * @param string $talksUri
     * @param array $data
     */
    public function addTalk($talksUri, $data)
    {
        array_walk($data, function (&$value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i');
            }
        });

        // ensure that speakers is a list of names with no empty ones
        if (isset($data['speakers'])) {
            array_walk($data['speakers'], function (&$value) {
                if (is_array($value)) {
                    $value = current($value);
                }
                $value = trim($value);
            });
            $data['speakers'] = array_filter($data['speakers']);
        }


        list ($status, $result, $headers) = $this->apiPost($talksUri, $data);
        // if successful, return talk entity represented by the URL in the Location header
        if ($status == 201) {
            $response = $this->getCollection($headers['location']);
            return current($response['talks']);
        }
        if ($status == 202) {
            return null;
        }
        if ($status == 400) {
            $decoded = json_decode($result);
            if (is_array($decoded)) {
                $result = current($decoded);
            }
        }

        throw new \Exception($result);
    }

    /**
     * Edit a talk
     *
     * @param string $talkUri
     * @param array $data
     */
    public function editTalk($talkUri, $data)
    {
        array_walk($data, function (&$value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i');
            }
        });

        // ensure that speakers is a list of names with no empty ones
        if (isset($data['speakers'])) {
            array_walk($data['speakers'], function (&$value) {
                if (is_array($value)) {
                    $value = current($value);
                }
                $value = trim($value);
            });
            $data['speakers'] = array_filter($data['speakers']);
        }

        list ($status, $result, $headers) = $this->apiPut($talkUri, $data);

        // if successful, return talk entity represented by the URL in the Location header
        if ($status == 204) {
            $response = $this->getCollection($headers['location']);
            return current($response['talks']);
        }

        $decoded = json_decode($result);
        if (is_array($decoded)) {
            $result = current($decoded);
        }

        throw new \RuntimeException($result);
    }

    public function claimTalk($talkSpeakersUri, $data)
    {
        list ($status, $result, $headers) = $this->apiPost($talkSpeakersUri, $data);

        if ($status == 204) {
            return true;
        }

        $result = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed: " . $message);
    }
    
    /**
     * Add a talk to a track
     *
     * @param  $talkTracksUri
     * @param  $trackUri
     *
     * @return  bool
     */
    public function addTalkToTrack($talkTracksUri, $trackUri)
    {
        $params = [
            'track_uri' => $trackUri,
        ];

        list($status, $result, $headers) = $this->apiPost($talkTracksUri, $params);
        if ($status == 201) {
            return true;
        }

        $result = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed: " . $message);
    }

    /**
     * Remove a talk from a track
     *
     * @param  $removeTrackUri
     *
     * @return  bool
     */
    public function removeTalkFromTrack($removeTrackUri)
    {
        list($status, $result, $headers) = $this->apiDelete($removeTrackUri);
        if ($status == 204) {
            return true;
        }

        $result = json_decode($result);
        $message = $result[0];

        throw new \Exception("Failed to remove talk from track: " . $message);
    }
}
