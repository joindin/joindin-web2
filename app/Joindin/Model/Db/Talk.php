<?php
namespace Joindin\Model\Db;

use Joindin\Service\Cache;

class Talk
{
    protected $keyName = 'talks';
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getUriFor($slug, $eventUri)
    {
        $data = $this->cache->loadByKeys('talks', array(
            'event_uri' => $eventUri,
            'slug' => $slug
        ));
        return $data['uri'];
    }

    public function getTalkByStub($stub)
    {
        $data = $this->cache->load('talks', 'stub', $stub);
        return $data;
    }

    public function load($uri)
    {
        $data = $this->cache->load('talks', 'uri', $uri);
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

        $savedTalk = $this->load($talk->getApiUri());
        if ($savedTalk) {
            // talk is already known - update this record
            $data = array_merge($savedTalk, $data);
        }

		$keys = array(
            'event_uri' => $talk->getEventUri(),
            'slug' => $talk->getUrlFriendlyTalkTitle()
        );

        $this->cache->save('talks', $data, 'uri', $talk->getApiUri());
        $this->cache->save('talks', $data, 'stub', $talk->getStub());
        $this->cache->saveByKeys('talks', $data, $keys);
    }
}
