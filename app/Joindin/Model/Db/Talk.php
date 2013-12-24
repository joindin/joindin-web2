<?php
namespace Joindin\Model\Db;

use Joindin\Service\Db as DbService;
use Joindin\Service\Helper\Slug;

class Talk
{
    protected $keyName = 'talks';
    protected $db;

    public function __construct()
    {
        $this->db = new DbService();
    }

    public function getUriFor($slug, $eventUri)
    {
        $data = $this->db->getOneByKeys($this->keyName, array(
            'event_uri' => $eventUri,
            'slug' => $slug
        ));
        return $data['uri'];
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
            'slug' => Slug::stringToSlug($talk->getTitle()),
            'verbose_uri' => $talk->getApiUri(true),
            'event_uri' => $talk->getEventUri(),
        );

        $mongoTalk = $this->load($talk->getApiUri());
        if ($mongoTalk) {
            // talk is already known - update this record
            $data = array_merge($mongoTalk, $data);
        }

        return $this->db->save($this->keyName, $data);
    }
}
