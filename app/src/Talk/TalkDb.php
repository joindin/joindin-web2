<?php
namespace Talk;

use Application\BaseDb;
use Application\CacheService;

class TalkDb extends BaseDb
{
    public function __construct(CacheService $cache)
    {
        parent::__construct($cache);
        $this->keyName = 'talks';
    }

    public function getUriFor($slug, $eventUri)
    {
        $data = $this->cache->loadByKeys($this->keyName, array(
            'event_uri' => $eventUri,
            'slug' => $slug
        ));

        if ($data) {
            return $data['uri'];
        }

        return null;
    }

    public function getSlugFor($talkUri)
    {
        $talk = $this->load('uri', $talkUri);
        if ($talk) {
            return $talk['slug'];
        }

        return null;
    }

    public function save(TalkEntity $talk)
    {
        $data = array(
            'uri' => $talk->getApiUri(),
            'title' => $talk->getTitle(),
            'slug' => $talk->getUrlFriendlyTalkTitle(),
            'verbose_uri' => $talk->getApiUri(true),
            'event_uri' => $talk->getEventUri(),
            'stub' => $talk->getStub(),
        );

        $savedTalk = $this->load('uri', $talk->getApiUri());
        if ($savedTalk) {
            // talk is already known - update this record
            $data = array_merge($savedTalk, $data);
        }

        $keys = array(
            'event_uri' => $talk->getEventUri(),
            'slug' => $talk->getUrlFriendlyTalkTitle()
        );
        $this->cache->saveByKeys($this->keyName, $data, $keys);
        $this->cache->save($this->keyName, $data, 'uri', $talk->getApiUri());
        $this->cache->save($this->keyName, $data, 'stub', $talk->getStub());
    }
}
