<?php
namespace Client;

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

class ClientController extends BaseController
{
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/client', array($this, 'index'))->name('clients');
        $app->map('/client/create', array($this, 'createClient'))->via('GET', 'POST')->name('client-create');
        $app->get('/client/:clientName', array($this, 'showClient'))->name('client-show');
        $app->map('/client/:clientName/edit', array($this, 'editClient'))->via('GET', 'POST')->name('client-edit');
        $app->get('/client/:clientName/delete', array($this, 'deleteClient'))->name('client-delete');
    }

    public function index()
    {
        if (!isset($_SESSION['user'])) {
            $thisUrl = $this->application->urlFor('clients');
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $clientApi = $this->getClientApi();
        $clients = $clientApi->getCollection([]);

        $this->render('Client/index.html.twig',['clients' => $clients['clients']]);
    }

    public function editClient($clientName)
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

    public function createClient()
    {
        if (!isset($_SESSION['user'])) {
            $thisUrl = $this->application->urlFor('talk-edit', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug]);
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $request = $this->application->request();
        $display_name = $request->post('display_name');
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
        
        $speakers = $talk->getSpeakers();
        $valid = false;
        foreach ($speakers as $speaker) {
            if (! isset($speaker->speaker_uri) && $speaker->speaker_name == $display_name) {
                $valid = true;
            }
        }

        if ($valid) {
            try {
                $talkApi->claimTalk(
                    $talk->getSpeakersUri(),
                    array(
                        'display_name'  => $display_name,
                        'username'      => $_SESSION['user']->getUsername()
                    )
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

        $url = $this->application->urlFor("talk", array('eventSlug' => $eventSlug, 'talkSlug' => $talkSlug));
        $this->application->redirect($url);
    }

    public function showClient($clientName)
    {
        if (!isset($_SESSION['user'])) {
            $thisUrl = $this->application->urlFor('client-show', ['clientName' => $clientName]);
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $clientApi = $this->getClientApi();
        $client = $clientApi->getById($clientName);

        if (!$client) {
            $this->application->notFound();
            return;
        }

        $this->render('Client/details.html.twig',['client' => $client]);
    }

    public function deleteClient($clientName)
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
                'client',
                array('eventSlug' => $event['url_friendly_name'], 'talkSlug' => $talk['slug'])
            )
        );
    }

    /**
     * @return ClientApi
     */
    public function getClientApi()
    {
        return new ClientApi($this->cfg, $this->accessToken);
    }
}
