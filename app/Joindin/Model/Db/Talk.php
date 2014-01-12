<?php
namespace Joindin\Model\Db;

use Joindin\Service\Db as DbService;

class Talk
{
    protected $keyName = 'talks';
    protected $db;

    public function __construct($dbName)
    {
        $this->db = new DbService($dbName);
    }

    public function getUriFor($slug, $eventUri)
    {
        $data = $this->db->getOneByKeys($this->keyName, array(
            'event_uri' => $eventUri,
            'slug' => $slug
        ));
        return $data['uri'];
    }

    public function getTalkByStub($stub)
    {
        $data = $this->db->getOneByKey($this->keyName, 'stub', $stub);
        return $data;
    }

    public function load($uri)
    {
        $data = $this->db->getOneByKey($this->keyName, 'uri', $uri);
        return $data;
    }

    public function saveSlugToDatabase(\Joindin\Model\Talk $talk)
    {
        $data = array(
            'uri' => $talk->getApiUri(),
            'title' => $talk->getTitle(),
            'slug' => $talk->getUrlFriendlyTalkTitle(),
            'verbose_uri' => $talk->getApiUri(true),
            'event_uri' => $talk->getEventUri(),
            'stub' => $talk->getStub(),
        );

        $mongoTalk = $this->load($talk->getApiUri());
        if ($mongoTalk) {
            // talk is already known - update this record
            $data = array_merge($mongoTalk, $data);
        }

        $criteria = array('uri' => $talk->getApiUri());

        return $this->db->save($this->keyName, $data, $criteria);
    }
}
