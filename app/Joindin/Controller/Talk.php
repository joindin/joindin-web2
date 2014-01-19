<?php
namespace Joindin\Controller;

use Joindin\Model\Db\Event as DbEvent;
use Joindin\Model\Db\Talk as DbTalk;
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
        $config = new Config();
        $eventApi = new \Joindin\Model\API\Event($config, $this->accessToken);
        $event = $eventApi->getByFriendlyUrl($eventSlug);
        $eventUri = $event->getUri();

        $dbName_ = $config->getConfig();
        $dbName = $dbName_['mongo']['database_name'];
        $talkDb = new DbTalk($dbName);
        $talkUri = $talkDb->getUriFor($talkSlug, $eventUri);

        $talkApi = new \Joindin\Model\API\Talk($config, $this->accessToken, new DbTalk($dbName));
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
        $config = new Config();
        $dbName_ = $config->getConfig();
        $dbName = $dbName_['mongo']['database_name'];
        $talkDb = new DbTalk($dbName);
        $talk = $talkDb->getTalkByStub($talkStub);



        $eventDb = new DbEvent($dbName);
        $event = $eventDb->load($talk['event_uri']);
        if (!$event) {
            throw new \Slim_Exception_Pass('Page not found', 404);
        }

        $this->application->redirect(
            $this->application->urlFor('talk', array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug']))
        );
    }



}
