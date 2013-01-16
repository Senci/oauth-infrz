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
    /**
     * @inheritdoc
     */
    public function mainAction()
    {
        $this->isGetRequest();
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }
        $clients = $this->db->getClientsFromUser($this->authFactory->getUser());

        $this->responseBuilder->buildClientOverview($clients);
    }

    /**
     * The object page to the client with the given id.
     */
    public function objectAction()
    {
        $this->isGetRequest();
        $client = $this->getClient();
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildClientPage($client, $page_token->token);
    }

    /**
     * The form to register a new client.
     */
    public function newAction()
    {
        $this->isGetRequest();
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildNewClient($page_token->token);
    }

    /**
     * Deletes the client with the given id.
     */
    public function deleteAction()
    {
        $this->isPostRequest();
        $client = $this->getClient();

        if (!$this->db->deleteClient($client)) {
            $this->responseBuilder->buildError(
                'internal_server_error',
                'There has been an error deleting the client.' .
                'Please retry and contact a system administrator if the error reoccurs.'
            );
        }
        $clients = $this->db->getClientsFromUser($this->authFactory->getUser());
        $success_message = sprintf('Yay, your client "%s" has successfully been deleted!', $client->name);
        $success = array('title' => 'Client successfully deleted', 'message' => $success_message);

        $this->responseBuilder->buildClientOverview($clients, $success);
    }

    /**
     * Call to register a new client.
     */
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

    /**
     * Form to edit a client.
     */
    public function editAction()
    {
        $this->isGetRequest();
        $client = $this->getClient();
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildClientEditPage($client, $page_token->token);
    }

    /**
     * Updates a client with the given parameters.
     */
    public function updateAction()
    {
        $this->isPostRequest();
        $client = $this->getClient();

        $name          = isset($_POST['name'])          ? $_POST['name'] : false;
        $description   = isset($_POST['description'])   ? $_POST['description'] : false;
        $redirect_uri  = isset($_POST['redirect_uri'])  ? $_POST['redirect_uri'] : false;
        $default_scope = isset($_POST['default_scope']) ? $_POST['default_scope'] : false;

        if (!$name or !$description or !$redirect_uri or !$default_scope) {
            $this->responseBuilder->buildError('missing_param');
        }

        $client->name = urldecode($name);
        $client->description = urldecode($description);
        $client->redirect_uri = urldecode($redirect_uri);
        $client->default_scope = json_decode(urldecode($default_scope));

        $client = $this->db->updateClient($client);

        header(sprintf('Location: /client/_%s', $client->id));
    }

    /**
     * Generates new credentials and updates them.
     */
    public function newCredentialsAction()
    {
        $this->isPostRequest();
        $client = $this->getClient();

        $client = $this->db->updateClientCredentials($client);

        header(sprintf('Location: /client/_%s', $client->id));
    }

    /**
     * Determines the client by the given id-parameter and returns it.
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
