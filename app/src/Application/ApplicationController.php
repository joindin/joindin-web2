<?php
namespace Application;

use Event\EventDb;
use Event\EventApi;
use User\UserDb;
use User\UserApi;

class ApplicationController extends BaseController
{
    protected function defineRoutes(\Slim\Slim $app)
    {
        $app->get('/', array($this, 'index'));
        $app->get('/apps', array($this, 'apps'))->name('apps');
        $app->get('/about', array($this, 'about'))->name('about');
        $app->map('/contact', array($this, 'contact'))->via('GET', 'POST')->name('contact');
        $app->get('/not-allowed', array($this, 'notAllowed'))->name('not-allowed');
        $app->get('/assets', array($this, 'assets'))->name('assets');
    }

    public function index()
    {
        $page = ((int)$this->application->request()->get('page') === 0)
            ? 1
            : $this->application->request()->get('page');

        $perPage = 10;
        $start = ($page -1) * $perPage;

        $eventApi = $this->getEventApi();
        $hotEvents = $eventApi->getEvents($perPage, $start, 'hot');
        $cfpEvents = $eventApi->getEvents(4, 0, 'cfp', true);

        $this->render(
            'Application/index.html.twig',
            array(
                'events' => $hotEvents,
                'cfp_events' => $cfpEvents,
                'page' => $page,
            )
        );
    }

    /**
     * Get latest current events
     *
     * @param $start
     * @param $perPage
     * @return array
     */
    public function getCurrentEvents($start, $perPage)
    {
        $eventApi = $this->getEventApi();
        return $eventApi->getEvents($perPage, $start, 'hot');
    }

    /**
     * Display the apps page
     */
    public function apps()
    {
        $this->render(
            'Application/apps.html.twig',
            [
                'hot_events' => $this->getCurrentEvents(0, 5),
            ]
        );
    }

    /**
     * Render the about page
     */
    public function about()
    {
        $this->render(
            'Application/about.html.twig',
            [
                'hot_events' => $this->getCurrentEvents(0, 5),
            ]
        );
    }

    /**
     * Render the contact page
     */
    public function contact()
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new ContactFormType());

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getData();

                $config = $this->application->config('oauth');
                $clientId = $config['client_id'];
                $clientSecret = $config['client_secret'];

                try {
                    $contactApi = $this->getContactApi();
                    $contactApi->contact(
                        $values['name'],
                        $values['email'],
                        $values['subject'],
                        $values['comment'],
                        $clientId,
                        $clientSecret
                    );
                    $this->application->flash('message', "Thank you for contacting us.");
                    $this->application->redirect($this->application->urlFor("contact"));
                } catch (\Exception $e) {
                    $this->application->flashNow('error', $e->getMessage());
                }
            }
        }

        $this->render(
            'Application/contact.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Render the assets page
     */
    public function assets()
    {
        $this->render(
            'Application/assets.html.twig',
            [
                'hot_events' => $this->getCurrentEvents(0, 5),
            ]
        );
    }


    /**
     * Render the notAllowed page
     */
    public function notAllowed()
    {

        $this->render('Application/not-allowed.html.twig', [
            'redirect' => $this->application->request->get('redirect')
        ]);
    }

    /**
     * @return EventApi
     */
    private function getEventApi()
    {
        return $this->application->container->get(EventApi::class);
    }

    /**
     * @return ContactApi
     */
    private function getContactApi()
    {
        return $this->application->container->get(ContactApi::class);
    }
}
