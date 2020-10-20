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
    protected function defineRoutes(Slim $app)
    {
        $app->get('/event/:eventSlug/:talkSlug', [$this, 'index'])->name('talk');
        $app->map('/event/:eventSlug/:talkSlug/edit', [$this, 'editTalk'])->via('GET', 'POST')->name('talk-edit');
        $app->post('/event/:eventSlug/:talkSlug/star', [$this, 'star'])->name('talk-star');
        $app->get('/talk/:talkStub', [$this, 'quick'])->name('talk-quicklink');
        $app->get('/event/:eventSlug/:talkSlug/comments/:commentHash/report', [$this, 'reportComment'])
            ->name('talk-report-comment');
        $app->post('/event/:eventSlug/:talkSlug/add-comment', [$this, 'addComment'])->name('talk-add-comment');
        $app->get('/:talkId', [$this, 'quickById'])
            ->name('talk-quick-by-id')
            ->conditions(['talkId' => '\d+']);
        $app->get('/talk/view/:talkId', [$this, 'quickById'])
            ->name('talk-by-id-web1')
            ->conditions(['talkId' => '\d+']);
        $app->post('/event/:eventSlug/:talkSlug/claim', [$this, 'claimTalk'])->name('talk-claim');
        $app->get('/event/:eventSlug/:talkSlug/unlink/:username', [$this, 'unlinkSpeaker'])
            ->name('unlink-speaker');
        $app->map('/event/:eventSlug/:talkSlug/delete', [$this, 'deleteTalk'])->via('GET', 'POST')->name('talk-delete');
    }

    public function index($eventSlug, $talkSlug)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($eventSlug);

        if (!$event) {
            return Slim::getInstance()->notFound();
        }

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            return Slim::getInstance()->notFound();
        }

        $comments = $talkApi->getComments($talk->getCommentsUri(), true, 0);

        $canRateTalk = true;
        if (isset($_SESSION['user'])) {
            foreach ($comments as $comment) {
                $canRateTalk = $comment->canRateTalk($_SESSION['user']->getUri());
            }
            if ($talk->isSpeaker($_SESSION['user']->getUri())) {
                $canRateTalk = false;
            }
        }

        $unclaimed = [];

        foreach ($talk->getSpeakers() as $speaker) {
            if (! isset($speaker->speaker_uri)) {
                $unclaimed[] = $speaker->speaker_name;
            }
        }

        $canEditTalk = false;
        if (isset($_SESSION['user'])) {
            $canEditTalk = ($talk->isSpeaker($_SESSION['user']->getUri()) || $event->getCanEdit());
        }

        $this->render(
            'Talk/index.html.twig',
            [
                'talk'        => $talk,
                'event'       => $event,
                'comments'    => $comments,
                'talkSlug'    => $talkSlug,
                'canEditTalk' => $canEditTalk,
                'canRateTalk' => $canRateTalk,
                'unclaimed'   => $unclaimed,
            ]
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
        $event    = $eventApi->getByFriendlyUrl($eventSlug);

        if (!$event) {
            $this->application->notFound();
            return;
        }

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            $this->application->notFound();
            return;
        }
        $talkId    = basename($talk['uri']);
        $talkMedia = $talkApi->getTalkLinksById($talkId);

        $isAdmin   = $event->getCanEdit();
        $isSpeaker = $talk->isSpeaker($_SESSION['user']->getUri());
        if (!($isAdmin || $isSpeaker)) {
            $this->application->flash('error', "You do not have permission to do this.");

            $talkUrl = $this->application->urlFor('talk', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
            $this->application->redirect($talkUrl);
        }

        $languageApi = $this->getLanguageApi();
        $languages   = $languageApi->getLanguagesChoiceList();

        $talkTypeApi = $this->getTalkTypeApi();
        $talkTypes   = $talkTypeApi->getTalkTypesChoiceList();

        $trackApi = $this->getTrackApi();
        $tracks   = $trackApi->getTracksChoiceList($event->getTracksUri());

        // default values
        $data                     = [];
        $data['talk_title']       = $talk->getTitle();
        $data['talk_description'] = $talk->getDescription();
        $data['start_date']       = $talk->getStartDateTime();
        $data['duration']         = $talk->getDuration();
        $data['language']         = $talk->getLanguage();
        $data['type']             = $talk->getType();
        if ($talk->getTracks()) {
            $data['track'] = $talk->getTracks()[0]->track_uri;
        }
        if ($talk->getSpeakers()) {
            foreach ($talk->getSpeakers() as $speaker) {
                $data['speakers'][] = ['name' => $speaker->speaker_name];
            }
        }

        foreach ($talkMedia as $media) {
            $data['talk_media'][$media->id] = [
                'type' => $media->display_name,
                'url'  => $media->url
            ];
        }

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(TalkFormType::class, $data, [
            'event'     => $event,
            'languages' => $languages,
            'talkTypes' => $talkTypes,
            'tracks'    => $tracks,
        ]);

        $request = $this->application->request();
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getdata();

                // not allowing speakers to remove themselves after a talk has started (JOINDIN-749)
                if (!$isAdmin && $data['start_date'] < new \DateTimeImmutable()) {
                    $values['speakers'] = $data['speakers'];
                }

                try {
                    $talkApi = $this->getTalkApi();
                    $talk    = $talkApi->editTalk($talk->getApiUri(), $values);

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
                'talk'     => $talk,
                'event'    => $event,
                'talkSlug' => $talkSlug,
                'form'     => $form->createView(),
            ]
        );
    }


    public function claimTalk($eventSlug, $talkSlug)
    {
        if (!isset($_SESSION['user'])) {
            $thisUrl = $this->application->urlFor('talk-edit', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $request      = $this->application->request();
        $display_name = $request->post('display_name');
        $eventApi     = $this->getEventApi();
        $event        = $eventApi->getByFriendlyUrl($eventSlug);

        if (!$event) {
            $this->application->notFound();
            return;
        }

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            $this->application->notFound();
            return;
        }

        $speakers = $talk->getSpeakers();
        $valid    = false;
        foreach ($speakers as $speaker) {
            if (! isset($speaker->speaker_uri) && $speaker->speaker_name == $display_name) {
                $valid = true;
            }
        }

        if ($valid) {
            try {
                $talkApi->claimTalk(
                    $talk->getSpeakersUri(),
                    [
                        'display_name'  => $display_name,
                        'username'      => $_SESSION['user']->getUsername()
                    ]
                );

                $this->application->flash(
                    'claimmessage',
                    'Your claim has been received. You will receive an' .
                    ' email once the host has accepted your claim'
                );
            } catch (Exception $e) {
                $this->application->flash('claimerror', $e->getMessage());
            }
        } else {
            $this->application->flash('claimerror', "No speaker {$display_name} found for this talk.");
        }

        $url = $this->application->urlFor("talk", ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
        $this->application->redirect($url);
    }

    public function star($eventSlug, $talkSlug)
    {
        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($eventSlug);
        $this->application->contentType('application/json');

        if (!$event) {
            $this->application->notFound();
            return;
        }

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        if (!$talk) {
            $this->application->notFound();
            return;
        }

        try {
            $result = $talkApi->toggleStar($talk);
            $this->application->status(200);
            echo json_encode($result);
        } catch (Exception $e) {
            $reason = $e->getMessage();
            $this->application->halt(500, '{ "message": "Failed to toggle star: ' . $reason .'" }');
        }
    }

    public function quick($talkStub)
    {
        $talkDb = $this->application->container->get(TalkDb::class);
        $talk   = $talkDb->load('stub', $talkStub);

        $eventDb = $this->application->container->get(EventDb::class);
        $event   = $eventDb->load('uri', $talk['event_uri']);
        if (!$event) {
            return \Slim\Slim::getInstance()->notFound();
        }

        $this->application->redirect(
            $this->application->urlFor(
                'talk',
                ['eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug']]
            )
        );
    }

    public function quickById($talkId)
    {
        $eventDb = $this->application->container->get(EventDb::class);

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkByTalkId($talkId);
        if (!$talk) {
            return \Slim\Slim::getInstance()->notFound();
        }

        $event = $eventDb->load('uri', $talk->getEventUri());
        if (!$event) {
            // load from API as not in cache
            $eventApi    = $this->getEventApi();
            $eventEntity = $eventApi->getEvent($talk->getEventUri());
            if (!$eventEntity) {
                return \Slim\Slim::getInstance()->notFound();
            }
            $event['url_friendly_name'] = $eventEntity->getUrlFriendlyName();
        }

        $this->application->redirect(
            $this->application->urlFor(
                'talk',
                ['eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk->getUrlFriendlyTalkTitle()]
            )
        );
    }

    public function addComment($eventSlug, $talkSlug)
    {
        $request = $this->application->request();
        $comment = trim(html_entity_decode($request->post('comment')));
        $rating  = (int) $request->post('rating');
        $url     = $this->application->urlFor("talk", ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);

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
        $event    = $eventApi->getByFriendlyUrl($eventSlug);

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
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
        $eventApi         = $this->getEventApi();
        $event            = $eventApi->getByFriendlyUrl($eventSlug);
        $reportedComment  = null;
        $talkApi          = $this->getTalkApi();
        $talk             = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        $url              = $this->application->urlFor("talk", ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);

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

    public function unlinkSpeaker($eventSlug, $talkSlug, $username)
    {
        $url = $this->application->urlFor('talk', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);

        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $url
            );
        }

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($eventSlug);
        $eventUri = $event->getUri();

        $talkApi = $this->getTalkApi();
        $talk    = $talkApi->getTalkBySlug($talkSlug, $eventUri);
        $talkUri = $talk->getApiUri();

        $userApi = $this->getUserApi();
        $user    = $userApi->getUserByUsername($username);
        $userId  = $user->getId();

        $unlinkSpeakerUri = $talkUri . "/speakers/" . $userId;

        $isAdmin = $event->getCanEdit();
        if (!$isAdmin && $talk->getStartDateTime() > new \DateTimeImmutable()) {
            try {
                $talkApi->unlinkVerifiedSpeakerFromTalk($unlinkSpeakerUri);
            } catch (Exception $e) {
                $this->application->flash('error', $e->getMessage());
                $this->application->redirect($url);
                return;
            }

            $this->application->flash('message', 'Speaker has been removed from this talk.');
            $this->application->redirect($url);
        } else {
            $this->application->flash('message', 'Speaker can not be removed after start of talk.');
            $this->application->redirect($url);
        }
    }

    public function deleteTalk($eventSlug, $talkSlug)
    {
        $thisUrl = $this->application->urlFor(
            'talk-delete',
            ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]
        );

        if (! isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $eventApi = $this->getEventApi();
        $event    = $eventApi->getByFriendlyUrl($eventSlug);

        if (! $event->getCanEdit()) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $talkApi = $this->getTalkApi();
        try {
            $talk = $talkApi->getTalkBySlug($talkSlug, $event->getUri());
        } catch (Exception $e) {
            $this->application->notFound();
            return;
        }

        if (false === $talk) {
            $this->application->notFound();
            return;
        }

        // default values
        $data              = [];
        $data['talk_uri']  = $talk->getApiUri();

        $factory = $this->application->formFactory;
        $form    = $factory->create(TalkDeleteFormType::class, $data);

        $request = $this->application->request();

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                try {
                    $talkApi->deleteTalk($talk->getApiUri());

                    $this->application->flash('message', sprintf(
                        'The talk "%s" has been permanently removed',
                        $talk->getTitle()
                    ));

                    $this->application->redirect(
                        $this->application->urlFor(
                            'event-default',
                            ['friendly_name' => $eventSlug]
                        )
                    );

                    return;
                } catch (\RuntimeException $e) {
                    $form->adderror(
                        new FormError('An error occurred while removing this talk: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Talk/delete-talk.html.twig',
            [
                'talk'    => $talk,
                'form'    => $form->createView(),
                'backUri' => $this->application->urlFor('talk', [
                    'eventSlug' => $eventSlug,
                    'talkSlug'  => $talkSlug,
                ]),
                'user'    => $_SESSION['user']
            ]
        );
    }

    /**
     * @return EventApi
     */
    private function getEventApi()
    {
        return $this->application->container->get(EventApi::class);
    }

    /**
     * @return TalkApi
     */
    private function getTalkApi()
    {
        return $this->application->container->get(TalkApi::class);
    }

    /**
     * @return UserApi
     */
    private function getUserApi()
    {
        return $this->application->container->get(UserApi::class);
    }

    /**
     * @return LanguageApi
     */
    protected function getLanguageApi()
    {
        return $this->application->container->get(LanguageApi::class);
    }

    /**
     * @return TalkTypeApi
     */
    protected function getTalkTypeApi()
    {
        return $this->application->container->get(TalkTypeApi::class);
    }

    /**
     * @return TrackApi
     */
    protected function getTrackApi()
    {
        return $this->application->container->get(TrackApi::class);
    }
}
