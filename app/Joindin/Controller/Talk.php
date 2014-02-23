<?php
namespace Joindin\Controller;

use Joindin\Model\Db\Event as DbEvent;
use Joindin\Model\Db\Talk as DbTalk;
use Joindin\Service\Cache as Cache;
use Joindin\Service\Helper\Config;

class Talk extends Base
{

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event/:eventSlug/:talkSlug', array($this, 'index'))->name('talk');
        $app->get('/talk/:talkStub', array($this, 'quick'));
    }


    public function index($eventSlug, $talkSlug)
    {
        $keyPrefix = $this->cfg['redis']['keyPrefix'];
        $cache = new Cache($keyPrefix);

        $eventApi = new \Joindin\Model\API\Event($this->cfg, $this->accessToken, new DbEvent($cache));
        $event = $eventApi->getByFriendlyUrl($eventSlug);
        $eventUri = $event->getUri();

        $talkDb = new DbTalk($keyPrefix);
        $talkUri = $talkDb->getUriFor($talkSlug, $eventUri);

        $talkApi = new \Joindin\Model\API\Talk($this->cfg, $this->accessToken, new DbTalk($keyPrefix));
        $talk = $talkApi->getTalk($talkUri, true);

        $comments = $talkApi->getComments($talk->getCommentUri(), true);

        try {
            echo $this->application->render(
                'Talk/index.html.twig',
                array(
                    'talk' => $talk,
                    'event' => $event,
                    'comments' => $comments,
                )
            );
        } catch (\Twig_Error_Runtime $e) {
            $this->application->render(
                'Error/app_load_error.html.twig',
                array(
                    'message' => sprintf(
                        'An exception has been thrown during the rendering of ' .
                        'a template ("%s").',
                        $e->getMessage()
                    ),
                    -1,
                    null,
                    $e
                )
            );
        }
    }

    public function quick($talkStub)
    {
        $keyPrefix = $this->cfg['redis']['keyPrefix'];
        $cache = new Cache($keyPrefix);
        $talkDb = new DbTalk($keyPrefix);
        $talk = $talkDb->getTalkByStub($talkStub);

        $eventDb = new DbEvent($cache);
        $event = $eventDb->load('uri', $talk['event_uri']);
        if (!$event) {
            throw new \Slim_Exception_Pass('Page not found', 404);
        }

        $this->application->redirect(
            $this->application->urlFor('talk', array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug']))
        );
    }



}
