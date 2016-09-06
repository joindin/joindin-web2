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
use Talk\TalkTypeApi;
use Language\LanguageApi;
use Event\TrackApi;
use Symfony\Component\Form\FormError;

class TalkController extends BaseController
{
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/event/:eventSlug/:talkSlug', array($this, 'index'))->name('talk');
        $app->map('/event/:eventSlug/:talkSlug/edit', array($this, 'editTalk'))->via('GET', 'POST')->name('talk-edit');
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
                'canEditTalk' => ($talk->isSpeaker($_SESSION['user']->getUri()) || $event->getCanEdit()),
            )
        );
    }

    public function editTalk($eventSlug, $talkSlug)
    {
        if (!isset($_SESSION['user'])) {
            $thisUrl = $this->application->urlFor('talk-edit', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $eventApi = $this->getEventApi();
        $event = $eventApi->getByFriendlyUrl($eventSlug);

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

        $isAdmin = $event->getCanEdit();
        $isSpeaker = $talk->isSpeaker($_SESSION['user']->getUri());
        if (!($isAdmin || $isSpeaker)) {
            $this->application->flash('error', "You do not have permission to do this.");
            
            $talkUrl = $this->application->urlFor('talk', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
            $this->application->redirect($talkUrl);
        }

        $languageApi = $this->getLanguageApi();
        $languages = $languageApi->getLanguagesChoiceList();

        $talkTypeApi = $this->getTalkTypeApi();
        $talkTypes = $talkTypeApi->getTalkTypesChoiceList();

        $trackApi = $this->getTrackApi();
        $tracks = $trackApi->getTracksChoiceList($event->getTracksUri());

        // default values
        $data = [];
        $data['talk_title'] = $talk->getTitle();
        $data['talk_description'] = $talk->getDescription();
        $data['slides_link'] = $talk->getSlidesLink();
        $data['start_date'] = $talk->getStartDateTime();
        $data['duration'] = $talk->getDuration();
        $data['language'] = $talk->getLanguage();
        $data['type'] = $talk->getType();
        if ($talk->getTracks()) {
            $data['track'] = $talk->getTracks()[0]->track_uri;
            
        }
        if ($talk->getSpeakers()) {
            foreach ($talk->getSpeakers() as $speaker) {
                $data['speakers'][] = ['name' => $speaker->speaker_name];
            }
        }

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form = $factory->create(new TalkFormType($event, $languages, $talkTypes, $tracks), $data);

        $request = $this->application->request();
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getdata();

                try {
                    $talkApi = $this->getTalkApi();
                    $talk = $talkApi->editTalk($talk->getApiUri(), $values);

                    if (!empty($values['track']) && isset($tracks[$values['track']])) {
                        $talkTracks = [];
                        foreach ($talk->getTracks() as $t) {
                            $talkTracks[$t->track_uri] = $t->remove_track_uri;
                        }

                        if (isset($talkTracks[$values['track']])) {
                            // submitted track is in current list of tracks.
                            unset($talkTracks[$values['track']]);
                        } else {
                            // submitted track is not in current list of tracks. Add it
                            $talkApi->addTalkToTrack($talk->getTracksUri(), $values['track']);
                        }

                        // remove all other tracks attached to this talk as we only handle one
                        // track per talk at the moment
                        foreach ($talkTracks as $remove_track_uri) {
                            $talkApi->removeTalkFromTrack($remove_track_uri);
                        }
                    }

                    $talkUrl = $this->application->urlFor('talk', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
                    $this->application->redirect($talkUrl);
                } catch (\RuntimeException $e) {
                    $form->adderror(
                        new FormError('An error occurred while adding this talk: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Talk/edit-talk.html.twig',
            [
                'talk' => $talk,
                'event' => $event,
                'talkSlug' => $talkSlug,
                'form' => $form->createView(),
            ]
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
                    $this->application->redirect($url);
                }
                if (stripos($e->getMessage(), 'comment failed spam check') !== false) {
                    // spam comment
                    $this->application->flash('error', 'Comment failed the spam check.');
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

    /**
     * @return LanguageApi
     */
    protected function getLanguageApi()
    {
        $languageApi = new LanguageApi($this->cfg, $this->accessToken);

        return $languageApi;
    }

    /**
     * @return TalkTypeApi
     */
    protected function getTalkTypeApi()
    {
        $talkTypeApi = new TalkTypeApi($this->cfg, $this->accessToken);

        return $talkTypeApi;
    }

    /**
     * @return TrackApi
     */
    protected function getTrackApi()
    {
        $trackApi = new TrackApi($this->cfg, $this->accessToken);

        return $trackApi;
    }
}
