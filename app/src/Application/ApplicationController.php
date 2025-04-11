<?php
namespace Application;

use Event\EventDb;
use Event\EventApi;
use Slim\Slim;
use Symfony\Component\Form\FormFactoryInterface;
use User\UserDb;
use User\UserApi;

class ApplicationController extends BaseController
{
    protected function defineRoutes(Slim $slim): void
    {
        $slim->get('/', [$this, 'index']);
        $slim->get('/about', [$this, 'about'])->name('about');
        $slim->map('/contact', [$this, 'contact'])->via('GET', 'POST')->name('contact');
        $slim->get('/not-allowed', [$this, 'notAllowed'])->name('not-allowed');
        $slim->get('/assets', [$this, 'assets'])->name('assets');
    }

    public function index(): void
    {
        $page = (int)$this->application->request()->get('page', 1);
        if ($page === 0) {
            $page = 1;
        }

        $perPage = 10;
        $start   = ($page -1) * $perPage;

        $eventApi       = $this->getEventApi();
        $upcomingEvents = $eventApi->getEvents($perPage, $start, 'upcoming');
        $cfpEvents      = $eventApi->getEvents(4, 0, 'cfp', true);

        $this->render(
            'Application/index.html.twig',
            [
                'events'     => $upcomingEvents,
                'cfp_events' => $cfpEvents,
                'page'       => $page,
            ]
        );
    }

    /**
     * Get latest current events
     */
    public function getCurrentEvents(int $start, int $perPage): array
    {
        return $this->getEventApi()->getEvents($perPage, $start, 'upcoming');
    }

    /**
     * Render the about page
     */
    public function about(): void
    {
        $this->render(
            'Application/about.html.twig',
            [
                'upcoming_events' => $this->getCurrentEvents(0, 5),
            ]
        );
    }

    /**
     * Render the contact page
     */
    public function contact(): void
    {
        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(new ContactFormType());

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getData();

                $config       = $this->application->config('oauth');
                $clientId     = $config['client_id'];
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
    public function assets(): void
    {
        $this->render(
            'Application/assets.html.twig',
            [
                'upcoming_events' => $this->getCurrentEvents(0, 5),
            ]
        );
    }


    /**
     * Render the notAllowed page
     */
    public function notAllowed(): void
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
