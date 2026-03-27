<?php
namespace Talk;

use Application\BaseDb;
use Application\CacheService;

class TalkDb extends BaseDb
{
    public function __construct(CacheService $cacheService)
    {
        parent::__construct($cacheService);
        $this->keyName = 'talks';
    }

    public function getUriFor($slug, $eventUri)
    {
        $data = $this->cache->loadByKeys($this->keyName, [
            'event_uri' => $eventUri,
            'slug'      => $slug
        ]);

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

    public function save(TalkEntity $talkEntity): void
    {
        $data = [
            'uri'         => $talkEntity->getApiUri(),
            'title'       => $talkEntity->getTitle(),
            'slug'        => $talkEntity->getUrlFriendlyTalkTitle(),
            'verbose_uri' => $talkEntity->getApiUri(true),
            'event_uri'   => $talkEntity->getEventUri(),
            'stub'        => $talkEntity->getStub(),
        ];

        $savedTalk = $this->load('uri', $talkEntity->getApiUri());
        if ($savedTalk) {
            // talk is already known - update this record
            $data = array_merge($savedTalk, $data);
        }

        $keys = [
            'event_uri' => $talkEntity->getEventUri(),
            'slug'      => $talkEntity->getUrlFriendlyTalkTitle()
        ];
        $this->cache->saveByKeys($this->keyName, $data, $keys);
        $this->cache->save($this->keyName, $data, 'uri', $talkEntity->getApiUri());
        $this->cache->save($this->keyName, $data, 'stub', $talkEntity->getStub());
    }
}
