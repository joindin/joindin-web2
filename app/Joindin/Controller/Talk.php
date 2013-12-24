<?php
namespace Joindin\Controller;


use Joindin\Model\Db\Event as DbEvent;
use Joindin\Model\Db\Talk as DbTalk;

class Talk extends Base
{

    protected function defineRoutes(\Slim $app)
    {
        $app->get('/event/:eventSlug/talk/:talkSlug', array($this, 'index'));
    }

    public function index($eventSlug, $talkSlug)
    {
        $eventDb = new DbEvent();
        $eventUri = $eventDb->getUriFor($eventSlug);
        $eventApi = new \Joindin\Model\API\Event($this->accessToken);
        $event = $eventApi->getBySlug($eventSlug);

        $talkDb = new DbTalk();
        $talkUri = $talkDb->getUriFor($talkSlug, $eventUri);
        $talkApi = new \Joindin\Model\API\Talk($this->accessToken, new DbTalk);
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
                        'An exception has been thrown during the rendering of '.
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


}
