<?php
namespace Talk;

use Application\BaseDb;

class TalkDb extends BaseDb
{
    protected $keyName = 'talks';

    public function getUriFor($slug, $eventUri)
    {
        $data = $this->cache->loadByKeys($this->keyName, array(
            'event_uri' => $eventUri,
            'slug' => $slug
        ));
        return $data['uri'];
    }

    public function getSlugFor($talkUri)
    {
        $talk = $this->load('uri', $talkUri);

        return $talk['slug'];
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
