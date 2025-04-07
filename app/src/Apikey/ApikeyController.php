<?php
namespace Apikey;

use Application\BaseController;
use Exception;
use Slim\Slim;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;

class ApikeyController extends BaseController
{
    /**
     * @var int number of results per pagination page
     */
    private int $resultsPerPage = 10;

    protected function defineRoutes(Slim $slim)
    {
        $slim->get('/user/:username/apikey', [$this, 'index'])->name('apikey-show');
        $slim->get('/user/:username/apikey/:apikey/delete', [$this, 'deleteApiKey'])->via('GET', 'POST')
                                                                                   ->name('apikey-delete');
    }

    public function index($username): void
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

        // construct pagination request data
        $queryParams = [
            'resultsperpage' => $this->resultsPerPage,
        ];

        // return user to last viewed page (if set)
        if (!isset($_GET['page'])
            && !empty($_SESSION['api_key_page'])
        ) {
            $page = $_SESSION['api_key_page'];
        } else {
            $page = (int) $this->application->request()->get('page');
        }

        // handle bad request data
        if ($page < 1) {
            $page = 1;
        }

        // define first record in result set
        if ($page > 1) {
            $queryParams['start'] = ($page - 1) * $this->resultsPerPage;
        }

        // save page position in case user returns
        $_SESSION['api_key_page'] = $page;

        $apikeyApi = $this->getApikeyApi();
        $tokens   = $apikeyApi->getCollection($queryParams);

        // reset session state if oauth_access_tokens are removed while user is logged in (found during testing)
        if (!isset($tokens['tokens'])) {
            unset($_SESSION['api_key_page']);
            $this->application->redirect($thisUrl);
        }

        // construct index values for page description
        $from = 1;
        $to   = $this->resultsPerPage;

        if ($page > 1) {
            $from = ($page - 1) * $this->resultsPerPage;
            $to   = $from + $this->resultsPerPage;

            if ($to > $tokens['pagination']->total) {
                $to = $tokens['pagination']->total;
            }
        }

        if ($to > $tokens['pagination']->total) {
            $to = $tokens['pagination']->total;
        }

        $this->render(
            'Apikey/index.html.twig',
            [
                'keys'       => $tokens['tokens'],
                'user'       => $_SESSION['user'],
                'pagination' => $tokens['pagination'],
                'from'       => $from,
                'to'         => $to,
                'perPage'    => $this->resultsPerPage,
                'page'       => $page,
            ]
        );
    }

    public function deleteApiKey($username, $apikey): void
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
        } catch (Exception $exception) {
            $this->application->notFound();
            return;
        }

        // default values
        $data               = [];
        $data['apikey_id']  = $apikey->getId();

        $factory = $this->application->formFactory;
        $form    = $factory->create(new ApikeyDeleteFormType(), $data);

        $request = $this->application->request();

        if ($request->isPost()) {
            $form->submit($request->post($form->getName()));

            if ($form->isValid()) {
                try {
                    $apikeyApi->deleteClient($apikey->getApiUri());

                    $this->application->flash(
                        'message',
                        sprintf(
                            'The API-Key %s has been permanently removed',
                            $apikey->getId()
                        )
                    );
                    $this->application->redirect(
                        $this->application->urlFor('apikey-show', ['username' => $username])
                    );
                    return;
                } catch (\RuntimeException $exception) {
                    $form->adderror(
                        new FormError('An error occurred while removing this API-Key: ' . $exception->getmessage())
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

    private function getApikeyApi(): \Apikey\ApikeyApi
    {
        return new ApikeyApi($this->cfg, $this->accessToken);
    }
}
