<?php
namespace Talk;

use Application\BaseController;
use Application\CacheService;
use Event\EventDb;
use Event\EventApi;
use Slim\Exception\Pass;

class TalkController extends BaseController
{

    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/event/:eventSlug/:talkSlug', array($this, 'index'))->name('talk');
        $app->get('/talk/:talkStub', array($this, 'quick'))->name('talk-quicklink');
        $app->post('/event/:eventSlug/:talkSlug/add-comment', array($this, 'addComment'))->name('talk-add-comment');
    }

    public function index($eventSlug, $talkSlug)
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);

        $event = $this->getEventApi()->getByFriendlyUrl($eventSlug);
        if (!$event) {
            $this->render(
                'Event/error_404.html.twig',
                array(
                    'message' => 'Event was not retrieved, perhaps the slug is invalid?',
                ),
                404
            );
            return;
        }

        $eventUri = $event->getUri();

        $talkDb = new TalkDb($cache);
        $talkUri = $talkDb->getUriFor($talkSlug, $eventUri);

        $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb);
        $talk = $talkApi->getTalk($talkUri, true);

        $comments = $talkApi->getComments($talk->getCommentUri(), true);

        $this->render(
            'Talk/index.html.twig',
            array(
                'talk' => $talk,
                'event' => $event,
                'comments' => $comments,
                'talkSlug' => $talkSlug,
            )
        );
    }

    public function quick($talkStub)
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        $cache = new CacheService($keyPrefix);
        $talkDb = new TalkDb($cache);
        $talk = $talkDb->load('stub', $talkStub);

        $eventDb = new EventDb($cache);
        $event = $eventDb->load('uri', $talk['event_uri']);
        if (!$event) {
            throw new Pass('Page not found', 404);
        }

        $this->application->redirect(
            $this->application->urlFor(
                'talk',
                array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug'])
            )
        );
    }

    public function addComment($eventSlug, $talkSlug)
    {
        $request = $this->application->request();
        $comment = trim(strip_tags($request->post('comment')));
        $rating = (int) $request->post('rating');
        $url = $this->application->urlFor("talk", array('eventSlug' => $eventSlug, 'talkSlug' => $talkSlug));

        if ($comment == '' || $rating == 0) {
            $this->application->flash('error', 'Please provide a comment and rating');
            $url .= '#add-comment';
            $this->application->redirect($url);
        }

        $cache = new CacheService($this->cfg['redisKeyPrefix']);
        $event = $this->getEventApi()->getByFriendlyUrl($eventSlug);

        $talkDb = new TalkDb($cache);
        $talkUri = $talkDb->getUriFor($talkSlug, $event->getUri());

        $talkApi = new TalkApi($this->cfg, $this->accessToken, $talkDb);
        $talk = $talkApi->getTalk($talkUri, true);
        if ($talk) {
            $talkApi->addComment($talk, $rating, $comment);
        }

        $this->application->flash('message', 'Thank you for your comment.');
        $url .= '#add-comment';
        $this->application->redirect($url);
    }

    /**
     * Returns the service used to talk to the API for events.
     *
     * @return EventApi
     */
    protected function getEventApi()
    {
        return $this->application->event_api_service;
    }
}
