<?php
namespace Talk;

use Application\BaseController;
use Application\CacheService;
use Event\EventDb;
use Event\EventApi;
use Slim_Exception_Pass;

class TalkController extends BaseController
{

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event/:eventSlug/:talkSlug', array($this, 'index'))->name('talk');
        $app->get('/talk/:talkStub', array($this, 'quick'));
    }


    public function index($eventSlug, $talkSlug)
    {
        $keyPrefix = $this->cfg['redis']['keyPrefix'];
        $cache = new CacheService($keyPrefix);

        $eventApi = new EventApi($this->cfg, $this->accessToken, new EventDb($cache));
        $event = $eventApi->getByFriendlyUrl($eventSlug);
        $eventUri = $event->getUri();

        $talkDb = new TalkDb($cache);
        $talkUri = $talkDb->getUriFor($talkSlug, $eventUri);

        $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb);
        $talk = $talkApi->getTalk($talkUri, true);

        $comments = $talkApi->getComments($talk->getCommentUri(), true);

        echo $this->render(
            'Talk/index.html.twig',
            array(
                'talk' => $talk,
                'event' => $event,
                'comments' => $comments,
            )
        );
    }

    public function quick($talkStub)
    {
        $keyPrefix = $this->cfg['redis']['keyPrefix'];
        $cache = new CacheService($keyPrefix);
        $talkDb = new TalkDb($cache);
        $talk = $talkDb->load('stub', $talkStub);

        $eventDb = new EventDb($cache);
        $event = $eventDb->load('uri', $talk['event_uri']);
        if (!$event) {
            throw new Slim_Exception_Pass('Page not found', 404);
        }

        $this->application->redirect(
            $this->application->urlFor('talk', array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug']))
        );
    }



}
