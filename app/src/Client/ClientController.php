<?php
namespace Client;

use Application\BaseController;
use Exception;
use Slim\Slim;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;

class ClientController extends BaseController
{
    protected function defineRoutes(Slim $app)
    {
        $app->get('/user/:username/client', [$this, 'index'])->name('clients');
        $app->map('/user/:username/client/create', [$this, 'createClient'])->via('GET', 'POST')
                                                                           ->name('client-create');
        $app->get('/user/:username/client/:clientName', [$this, 'showClient'])->name('client-show');
        $app->map('/user/:username/client/:clientName/edit', [$this, 'editClient'])->via('GET', 'POST')
                                                                                   ->name('client-edit');
        $app->get('/user/:username/client/:clientName/delete', [$this, 'deleteClient'])->via('GET', 'POST')
                                                                                       ->name('client-delete');
    }

    public function index($username)
    {
        $thisUrl = $this->application->urlFor('clients', [
            'username' => $username,
        ]);
        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        if ($_SESSION['user']->getUsername() !== $username) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $clientApi = $this->getClientApi();
        $clients   = $clientApi->getCollection([]);

        $this->render('Client/index.html.twig', [
            'clients' => $clients['clients'],
            'user'    => $_SESSION['user'],
        ]);
    }

    public function showClient($username, $clientName)
    {
        $thisUrl = $this->application->urlFor('client-show', [
            'clientName' => $clientName,
            'username'   => $username,
        ]);

        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        if ($_SESSION['user']->getUsername() !== $username) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $clientApi = $this->getClientApi();
        try {
            $client = $clientApi->getById($clientName);
        } catch (Exception $e) {
            $this->application->notFound();
            return;
        }

        $this->render('Client/details.html.twig', [
            'client' => $client,
            'user'   => $_SESSION['user']
        ]);
    }

    public function createClient($username)
    {
        $thisUrl = $this->application->urlFor('client-create', [
            'username' => $username,
        ]);

        if (! isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        if ($_SESSION['user']->getUsername() !== $username) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $request = $this->application->request();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(ClientFormType::class);

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid() && $this->addClientUsingForm($form)) {
                $this->application->redirect($this->application->urlFor('clients', [
                    'username' => $username,
                ]));
                return ;
            }
        }

        $this->render(
            'Client/submit.html.twig',
            [
                'form'    => $form->createView(),
                'backUri' => $this->application->urlFor('clients', [
                    'username' => $username,
                ]),
                'user' => $_SESSION['user']
            ]
        );
    }

    public function editClient($username, $clientName)
    {
        $thisUrl = $this->application->urlFor('client-edit', [
            'clientName' => $clientName,
            'username'   => $username,
        ]);

        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        if ($_SESSION['user']->getUsername() !== $username) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $clientApi = $this->getClientApi();
        try {
            $client = $clientApi->getById($clientName);
        } catch (Exception $e) {
            $this->application->notFound();
            return;
        }

        // default values
        $data                 = [];
        $data['application']  = $client->getName();
        $data['description']  = $client->getDescription();
        $data['callback_url'] = $client->getCallbackUrl();

        /** @var FormFactoryInterface $factory */
        $factory = $this->application->formFactory;
        $form    = $factory->create(ClientFormType::class, $data);

        $request = $this->application->request();
        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                $values = $form->getdata();

                try {
                    $clientApi->editClient($client->getApiUri(), $values);

                    $this->application->redirect(
                        $this->application->urlFor('client-show', [
                            'clientName' => $clientName,
                            'username'   => $username,
                        ])
                    );
                    return;
                } catch (\RuntimeException $e) {
                    $form->adderror(
                        new FormError('An error occurred while editing this client: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Client/edit-client.html.twig',
            [
                'client'  => $client,
                'form'    => $form->createView(),
                'backUri' => $this->application->urlFor('client-show', [
                    'clientName' => $client->getId(),
                    'username'   => $username,
                ]),
                'user' => $_SESSION['user'],
            ]
        );
    }


    /**
     * Submits the form data to the API and returns the newly created event, false if there is an error or null
     * if it is held for moderation.
     *
     * Should an error occur will this method append an error message to the form's error collection.
     *
     * @param Form $form
     *
     * @return ClientEntity|null|false
     */
    private function addClientUsingForm(Form $form)
    {
        $clientApi = $this->getClientApi();
        $values    = $form->getData();

        $result = false;
        try {
            $result = $clientApi->submit($values);
        } catch (\Exception $e) {
            $form->addError(
                new FormError('an error occurred while submitting your client: ' . $e->getMessage())
            );
        }

        return $result;
    }



    public function deleteClient($username, $clientName)
    {
        $thisUrl = $this->application->urlFor('client-delete', [
            'clientName' => $clientName,
            'username'   => $username,
        ]);

        if (!isset($_SESSION['user'])) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        if ($_SESSION['user']->getUsername() !== $username) {
            $this->application->redirect(
                $this->application->urlFor('not-allowed') . '?redirect=' . $thisUrl
            );
        }

        $clientApi = $this->getClientApi();
        try {
            $client = $clientApi->getById($clientName);
        } catch (Exception $e) {
            $this->application->notFound();
            return;
        }

        // default values
        $data               = [];
        $data['client_id']  = $client->getId();

        $factory = $this->application->formFactory;
        $form    = $factory->create(ClientDeleteFormType::class, $data);

        $request = $this->application->request();

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                try {
                    $clientApi->deleteClient($client->getApiUri());

                    $this->application->flash('notice', sprintf(
                        'The client %s has been permanently removed',
                        $client->getName()
                    ));
                    $this->application->redirect(
                        $this->application->urlFor('clients', ['username' => $username])
                    );
                    return;
                } catch (\RuntimeException $e) {
                    $form->adderror(
                        new FormError('An error occurred while removing this client: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Client/delete-client.html.twig',
            [
                'client'  => $client,
                'form'    => $form->createView(),
                'backUri' => $this->application->urlFor('client-show', [
                    'clientName' => $client->getId(),
                    'username'   => $username
                ]),
                'user' => $_SESSION['user']
            ]
        );
    }

    /**
     * @return ClientApi
     */
    private function getClientApi()
    {
        return $this->application->container->get(ClientApi::class);
    }
}
