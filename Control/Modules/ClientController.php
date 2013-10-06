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
     *
     * @Route("/client")
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
     *
     * @Route("/client/{client_id}/object")
     */
    public function objectAction()
    {
        $this->isGetRequest();
        $client = $this->getClient();
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildClient($client, $page_token->token);
    }

    /**
     * The form to register a new client.
     *
     * @Route("/client/new")
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
     *
     * @Route("/client/{client_id}/delete")
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
     *
     * @Route("/client/register")
     */
    public function registerAction()
    {
        $this->isPostRequest();
        if (!$this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }

        $name          = isset($_POST['name'])          ? $_POST['name'] : false;
        $description   = isset($_POST['description'])   ? $_POST['description'] : false;
        $host          = isset($_POST['host'])          ? $_POST['host'] : false;
        $redirect_uri  = isset($_POST['redirect_uri'])  ? $_POST['redirect_uri'] : false;
        $scope = isset($_POST['scope']) ? $_POST['scope'] : false;

        if (!$name or !$description or !$host or !$redirect_uri or !$scope) {
            $this->responseBuilder->buildError('missing_param');
        }

        $name = urldecode($name);
        $user = $this->authFactory->getUser();
        $description = urldecode($description);
        $host = explode(',', str_replace(' ', '', urldecode($host)));
        if ((count($host) <= 1) and (empty($host[0]))) {
            $host = array();
        }
        $redirect_uri = urldecode($redirect_uri);
        $scope = json_decode(urldecode($scope));

        $client = $this->db->insertClient($name, $user, $description, $host, $redirect_uri, $scope);

        header(sprintf('Location: %sclient/_%s', $this->config['baseurl'], $client->id));
    }

    /**
     * Form to edit a client.
     *
     * @Route("/client/{client_id}/edit")
     */
    public function editAction()
    {
        $this->isGetRequest();
        $client = $this->getClient();
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildClientEdit($client, $page_token->token);
    }

    /**
     * Updates a client with the given parameters.
     *
     * @Route("/client/{client_id}/update")
     */
    public function updateAction()
    {
        $this->isPostRequest();
        $client = $this->getClient();

        $name          = isset($_POST['name'])          ? $_POST['name'] : false;
        $description   = isset($_POST['description'])   ? $_POST['description'] : false;
        $host          = isset($_POST['host'])          ? $_POST['host'] : false;
        $redirect_uri  = isset($_POST['redirect_uri'])  ? $_POST['redirect_uri'] : false;
        $scope         = isset($_POST['scope'])         ? $_POST['scope'] : false;

        if (!$name or !$description or !$host or !$redirect_uri or !$scope) {
            $this->responseBuilder->buildError('missing_param');
        }

        $client->name = urldecode($name);
        $client->description = urldecode($description);
        $host = explode(',', str_replace(' ', '', urldecode($host)));
        if ((count($host) <= 1) and (empty($host[0]))) {
            $client->host = array();
        } else {
            $client->host = $host;
        }
        $client->redirect_uri = urldecode($redirect_uri);
        $client->scope = json_decode(urldecode($scope));
        $client = $this->db->updateClient($client);

        header(sprintf('Location: %sclient/_%s', $this->config['baseurl'], $client->id));
    }

    /**
     * Generates new credentials and updates them.
     *
     * @Route("/client/{client_id}/new_credentials")
     */
    public function newCredentialsAction()
    {
        $this->isPostRequest();
        $client = $this->getClient();

        $client = $this->db->updateClientCredentials($client);

        header(sprintf('Location: %sclient/_%s', $this->config['baseurl'], $client->id));
    }

    /**
     * Determines the client by the given id-parameter and returns it.
     *
     * @Route("/client/{client_id}/new_credentials")
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
        if (!($client instanceof Client) or ($client->user_id != $this->authFactory->getUser()->id)) {
            $this->responseBuilder->buildError('not_found');
        }

        return $client;
    }
}
