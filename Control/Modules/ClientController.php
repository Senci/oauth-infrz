<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;
use Infrz\OAuth\Model\Client;

class ClientController extends AbstractController
{
    public function mainAction()
    {
        $this->isGetRequest();
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }
        $clients = $this->db->getClientsFromUser($this->authFactory->getUser());

        $this->responseBuilder->buildClientOverview($clients);
    }

    public function objectAction()
    {
        $this->isGetRequest();
        $client = $this->getClient();

        $this->responseBuilder->buildClientPage($client);
    }

    public function newAction()
    {
        $this->isGetRequest();
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }

        $this->responseBuilder->buildNewClient();

    }

    public function registerAction()
    {
        $this->isPostRequest();
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }

        $name          = isset($_POST['name'])          ? $_POST['name'] : false;
        $description   = isset($_POST['description'])   ? $_POST['description'] : false;
        $redirect_uri  = isset($_POST['redirect_uri'])  ? $_POST['redirect_uri'] : false;
        $default_scope = isset($_POST['default_scope']) ? $_POST['default_scope'] : false;

        if (!$name or !$description or !$redirect_uri or !$default_scope) {
            $this->responseBuilder->buildError('missing_param');
        }

        $name = urldecode($name);
        $user = $this->authFactory->getUser();
        $description = urldecode($description);
        $redirect_uri = urldecode($redirect_uri);
        $default_scope = json_decode(urldecode($default_scope));

        $client = $this->db->insertClient($name, $user, $description, $redirect_uri, $default_scope);

        header(sprintf('Location: /client/_%s', $client->id));

        $this->responseBuilder->buildNewClient();

    }

    public function editAction()
    {
        $this->isGetRequest();
        $client = $this->getClient();

        $this->responseBuilder->buildClientEditPage($client);
    }

    public function updateAction()
    {
        $this->isPostRequest();
        $client = $this->getClient();

        $id            = isset($_POST['id'])            ? $_POST['id'] : false;
        $name          = isset($_POST['name'])          ? $_POST['name'] : false;
        $description   = isset($_POST['description'])   ? $_POST['description'] : false;
        $redirect_uri  = isset($_POST['redirect_uri'])  ? $_POST['redirect_uri'] : false;
        $default_scope = isset($_POST['default_scope']) ? $_POST['default_scope'] : false;

        if (!$id or !$name or !$description or !$redirect_uri or !$default_scope) {
            $this->responseBuilder->buildError('missing_param');
        }

        $client->name = urldecode($name);
        $client->description = urldecode($description);
        $client->redirect_uri = urldecode($redirect_uri);
        $client->default_scope = json_decode(urldecode($default_scope));

        $this->db->updateClient($client);

        header(sprintf('Location: /client/_%s', $id));
    }

    /**
     * Returns the Client
     */
    protected function getClient()
    {
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }
        $request = array_merge($_GET, $_POST);
        $id = isset($request['id']) ? $request['id'] : false;
        if (!$id) {
            $this->responseBuilder->buildError('missing_param');
        }
        $client = $this->db->getClientById($id);
        $clientsFromUser = $this->db->getClientsFromUser($this->authFactory->getUser());
        if (!$client instanceof Client or !(in_array($client, $clientsFromUser))) {
            $this->responseBuilder->buildError('not_found');
        }

        return $client;
    }
}
