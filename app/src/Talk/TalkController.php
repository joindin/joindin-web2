<?php
namespace Talk;

use Application\BaseController;
use Application\CacheService;
use Event\EventDb;
use Event\EventApi;
use User\UserDb;
use User\UserApi;
use Exception;
use Slim\Slim;

class TalkController extends BaseController
{
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/event/:eventSlug/:talkSlug', array($this, 'index'))->name('talk');
        $app->post('/event/:eventSlug/:talkSlug/star', array($this, 'star'))->name('talk-star');
        $app->get('/talk/:talkStub', array($this, 'quick'))->name('talk-quicklink');
        $app->get('/event/:eventSlug/:talkSlug/comments/:commentHash/report', array($this, 'reportComment'))
            ->name('talk-report-comment');
        $app->post('/event/:eventSlug/:talkSlug/add-comment', array($this, 'addComment'))->name('talk-add-comment');
        $app->get('/:talkId', array($this, 'quickById'))
            ->name('talk-quick-by-id')
            ->conditions(array('talkId' => '\d+'));
        $app->get('/talk/view/:talkId', array($this, 'quickById'))
            ->name('talk-by-id-web1')
            ->conditions(array('talkId' => '\d+'));
    }

    public function index($eventSlug, $talkSlug)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

        if (!$event) {
            return Slim::getInstance()->notFound();
        }

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            return Slim::getInstance()->notFound();
        }

        $comments = $talkApi->getComments($talk->getCommentUri(), true, 0);

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

    public function star($eventSlug, $talkSlug)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);
        $this->application->contentType('application/json');

        if (!$event) {
            $this->application->notFound();
            return;
        }

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            $this->application->notFound();
            return;
        }

        try {
            $result = $talkApi->toggleStar($talk);
        } catch (Exception $e) {
            $reason = $e->getMessage();
            $this->application->halt(500, '{ "message": "Failed to toggle star: ' . $reason .'" }');
        }

        $this->application->status(200);
        echo json_encode($result);
    }

    public function quick($talkStub)
    {
        $cache = $this->getCache();
        $talkDb = new TalkDb($cache);
        $talk = $talkDb->load('stub', $talkStub);

        $eventDb = new EventDb($cache);
        $event = $eventDb->load('uri', $talk['event_uri']);
        if (!$event) {
            return \Slim\Slim::getInstance()->notFound();
        }

        $this->application->redirect(
            $this->application->urlFor(
                'talk',
                array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug'])
            )
        );
    }

    public function quickById($talkId)
    {
        $cache = $this->getCache();
        $eventDb = new EventDb($cache);

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkByTalkId($talkId);
        if (!$talk) {
            return \Slim\Slim::getInstance()->notFound();
        }

        $event = $eventDb->load('uri', $talk->getEventUri());
        if (!$event) {
            // load from API as not in cache
            $eventApi = $this->getEventApi();
            $eventEntity = $eventApi->getEvent($talk->getEventUri());
            if (!$eventEntity) {
                return \Slim\Slim::getInstance()->notFound();
            }
            $event['url_friendly_name'] = $eventEntity->getUrlFriendlyName();
        }

        $this->application->redirect(
            $this->application->urlFor(
                'talk',
                array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk->getUrlFriendlyTalkTitle())
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

            if ($comment != '') {
                //If the user provided a comment but no rating, send the comment back
                $this->application->flash('comment', $comment);
            } else {
                //Otherwise, they provided a rating but no comment
                $this->application->flash('rating', $rating);
            }
            $url .= '#add-comment';
            $this->application->redirect($url);
        }

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if ($talk) {
            try {
                $talkApi->addComment($talk, $rating, $comment);
            } catch (Exception $e) {
                if (stripos($e->getMessage(), 'duplicate comment') !== false) {
                    // duplicate comment
                    $this->application->flash('error', 'Duplicate comment.');

                    //Pass the comment and rating back to re-populate the form
                    $this->application->flash('comment', $comment);
                    $this->application->flash('rating', $rating);

                    $url .= '#add-comment';

                    $this->application->redirect($url);
                }
                if (stripos($e->getMessage(), 'comment failed spam check') !== false) {
                    // spam comment
                    $this->application->flash('error', 'Comment failed the spam check.');

                    //Pass the comment and rating back to re-populate the form
                    $this->application->flash('comment', $comment);
                    $this->application->flash('rating', $rating);

                    $url .= '#add-comment';

                    $this->application->redirect($url);
                }
                throw $e;
            }
        }

        $this->application->flash('message', 'Thank you for your comment.');
        $url .= '#add-comment';
        $this->application->redirect($url);
    }

    public function reportComment($eventSlug, $talkSlug, $commentHash)
    {
        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);
        $talkApi = $this->getTalkApi();
        $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        $url = $this->application->urlFor("talk", array('eventSlug' => $eventSlug, 'talkSlug' => $talkSlug));

        $comments = $talkApi->getComments($talk->getCommentsUri());
        foreach ($comments as $comment) {
            if ($comment->getCommentHash() !== $commentHash) {
                continue;
            }
            $reportedComment = $comment;
            break;
        }

        if (!isset($reportedComment)) {
            $this->application->flash('error', 'The reported comment was not found on this talk.');
            $this->application->redirect($url);
        }

        try {
            $talkApi->reportComment($reportedComment->getReportedUri());
        } catch (Exception $e) {
            $this->application->flash('error', $e->getMessage());
            $this->application->redirect($url);
        }

        $this->application->flash('message', 'Thank you for your report.');
        $this->application->redirect($url);
    }

    /**
     * @return CacheService
     */
    private function getCache()
    {
        $keyPrefix = $this->cfg['redisKeyPrefix'];
        return new CacheService($keyPrefix);
    }

    /**
     * @return EventApi
     */
    private function getEventApi()
    {
        $eventDb = new EventDb($this->getCache());
        return new EventApi($this->cfg, $this->accessToken, $eventDb, $this->getUserApi());
    }

    /**
     * @return TalkApi
     */
    private function getTalkApi()
    {
        $talkDb = new TalkDb($this->getCache());
        return new TalkApi($this->cfg, $this->accessToken, $talkDb, $this->getUserApi());
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        $userDb = new UserDb($this->getCache());
        return new UserApi($this->cfg, $this->accessToken, $userDb);
    }
}
