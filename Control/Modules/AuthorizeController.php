<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;
use Infrz\OAuth\Model\Client;

class AuthorizeController extends AbstractController
{
    /**
     * @inheritdoc
     *
     * @Route("/authorize")
     */
    public function mainAction()
    {
        $this->isGetRequest();

        // set all needed GET-Variables to ${get-variable-name} if set
        $client_id    = isset($_GET['client_id'])     ? $_GET['client_id'] : false;
        $redirect_uri = isset($_GET['redirect_uri'])  ? $_GET['redirect_uri'] : false;

        if (!$client_id or !$redirect_uri) {
            $this->responseBuilder->buildError('missing_param');
        }
        if (!$client = $this->db->getClientByClientId($client_id)) {
            $this->responseBuilder->buildError('invalid_param', 'The given client_id is invalid.');
        }
        if (!$this->authFactory->isAuthenticated()) {
            $currentURL = sprintf('https://%s%s', $_SERVER["SERVER_NAME"], $_SERVER["REQUEST_URI"]);
            $this->responseBuilder->buildLogin($currentURL, false, $client);
        }
        if ($client->redirect_uri != urldecode($redirect_uri)) {
            $this->responseBuilder->buildError('invalid_param', 'The given redirect_uri is invalid.');
        }
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildAuthorize($client, $page_token->token, $redirect_uri);
    }

    /**
     * Displaying information about the access grant and redirecting to client-site with code.
     *
     * @Route("/authorize/grant")
     */
    public function grantAction()
    {
        $this->isPostRequest();

        $client_id    = isset($_POST['client_id'])     ? urldecode($_POST['client_id']) : false;
        $redirect_uri = isset($_POST['redirect_uri'])  ? urldecode($_POST['redirect_uri']): false;
        $scope        = isset($_POST['scope'])         ? urldecode($_POST['scope']) : false;

        if (!$client = $this->db->getClientByClientId($client_id)) {
            $this->responseBuilder->buildError('not_found');
        }

        $user = $this->authFactory->getUser();

        $scope = json_decode($scope);

        if (!($client instanceof Client) or !$redirect_uri or !$scope) {
            $this->responseBuilder->buildError('missing_param');
        }
        // calculate the scope that is missing but required by the client.
        $requiredButNotGrantedScope = array_diff($client->scope->required, $scope);
        if (!empty($requiredButNotGrantedScope)) {
            $err_msg = sprintf(
                'The scope you have selected is insufficient! "%s" requires your to add <i>%s</i> to your scope.',
                $client->name,
                implode(', ', $requiredButNotGrantedScope)
            );
            $this->responseBuilder->buildError('missing_param', $err_msg);
        }

        $auth_code = $this->db->insertAuthCode($client, $user, $scope);
        if (!strpos($redirect_uri, '?')) {
            $redirect_uri = sprintf('%s?code=%s', $redirect_uri, $auth_code->code);
        } else {
            $redirect_uri = rtrim($redirect_uri, '&');
            $redirect_uri = sprintf('%s&code=%s', $redirect_uri, $auth_code->code);
        }

        if ($this->config['direct_redirect']) {
            header(sprintf('Location: %s', $redirect_uri));
        }
        $this->responseBuilder->buildAuthorizeGranted($client, $redirect_uri, $scope);
    }

    /**
     * @Route("/authorize")
     */
    public function tokenAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->responseBuilder->buildError('not_found');
        }

        $grant_type    = isset($_POST['grant_type'])    ? urldecode($_POST['grant_type']) : false;
        $client_id     = isset($_POST['client_id'])     ? urldecode($_POST['client_id']) : false;
        $client_secret = isset($_POST['client_secret']) ? urldecode($_POST['client_secret']) : false;
        $code          = isset($_POST['code'])          ? urldecode($_POST['code']) : false;
        $redirect_uri  = isset($_POST['redirect_uri'])  ? urldecode($_POST['redirect_uri']) : false;

        if (!$grant_type or !$client_id or !$client_secret or !$code or !$redirect_uri) {
            $this->responseBuilder->buildJsonError('missing_param');
        }
        if (!$client = $this->db->getClientByClientId($client_id)) {
            $this->responseBuilder->buildJsonError('not_found');
        }
        if ($client_secret != $client->client_secret) {
            $this->responseBuilder->buildJsonError('no_permission');
        }

        if ($grant_type == 'authorization_code') {
            if (!$auth_code = $this->db->getAuthCodeByCode($code)) {
                $this->responseBuilder->buildJsonError('not_found');
            }
            if ($client->id != $auth_code->client_id) {
                $this->responseBuilder->buildJsonError('no_permission');
            }
            $user = $this->db->getUserById($auth_code->user_id);
            $access_token = $this->db->insertAccessToken($client, $user, $auth_code->scope);
            $this->db->deleteAuthCode($auth_code->code);
        } elseif ($grant_type == 'refresh_token') {
            if (!$refresh_token = $this->db->getRefreshTokenByToken($code)) {
                $this->responseBuilder->buildJsonError('not_found');
            }
            if (!$access_token = $this->db->getAccessTokenById($refresh_token->access_token_id)) {
                $this->responseBuilder->buildJsonError('not_found');
            }
            if ($client->id != $access_token->client_id) {
                $this->responseBuilder->buildJsonError('no_permission');
            }
            $user = $this->db->getuserbyid($access_token->user_id);
            $access_token_new = $this->db->insertAccessToken($client, $user, $access_token->scope);
            $this->db->deleteRefreshToken($refresh_token->token);
            $this->db->deleteAccessToken($access_token->token);
            $access_token = $access_token_new;
        } else {
            $this->responseBuilder->buildJsonError('invalid_param');
        }
        $refresh_token = $this->db->insertRefreshToken($access_token);
        $this->responseBuilder->buildAccessToken($access_token, $refresh_token);
    }
}
