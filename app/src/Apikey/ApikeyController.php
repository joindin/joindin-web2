<?php
namespace JoindIn\Web\Apikey;

use Exception;
use JoindIn\Web\Application\BaseController;
use RuntimeException;
use Slim\Slim;
use Symfony\Component\Form\FormError;

class ApikeyController extends BaseController
{
    protected function defineRoutes(Slim $app)
    {
        $app->get('/user/:username/apikey', [$this, 'index'])->name('apikey-show');
        $app->get('/user/:username/apikey/:apikey/delete', [$this, 'deleteApiKey'])->via('GET', 'POST')->name('apikey-delete');
    }

    public function index($username)
    {
        $thisUrl = $this->application->urlFor('apikey-show', ['username' => $username]);

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

        $tokenApi = $this->getApikeyApi();
        $tokens   = $tokenApi->getCollection([]);

        $this->render('Apikey/index.html.twig', ['keys' => $tokens['tokens'], 'user' => $_SESSION['user']]);
    }

    public function deleteApiKey($username, $apikey)
    {
        $thisUrl = $this->application->urlFor('apikey-delete', ['apikey' => $apikey, 'username' => $username]);

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

        $apikeyApi = $this->getApikeyApi();
        try {
            $apikey = $apikeyApi->getById($apikey);
        } catch (Exception $e) {
            $this->application->notFound();
            return;
        }

        // default values
        $data              = [];
        $data['apikey_id'] = $apikey->getId();

        $factory = $this->application->formFactory;
        $form    = $factory->create(new ApikeyDeleteFormType(), $data);

        $request = $this->application->request();

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                try {
                    $apikeyApi->deleteClient($apikey->getApiUri());

                    $this->application->flash('message', sprintf(
                        'The API-Key %s has been permanently removed',
                        $apikey->getId()
                    ));
                    $this->application->redirect(
                        $this->application->urlFor('apikey-show', ['username' => $username])
                    );
                    return;
                } catch (RuntimeException $e) {
                    $form->adderror(
                        new FormError('An error occurred while removing this API-Key: ' . $e->getmessage())
                    );
                }
            }
        }

        $this->render(
            'Apikey/delete.html.twig',
            [
                'apikey'  => $apikey,
                'form'    => $form->createView(),
                'backUri' => $this->application->urlFor('apikey-show', ['username' => $username]),
                'user'    => $_SESSION['user'],
            ]
        );
    }

    /**
     * @return ApikeyApi
     */
    private function getApikeyApi()
    {
        return new ApikeyApi($this->cfg, $this->accessToken);
    }
}
