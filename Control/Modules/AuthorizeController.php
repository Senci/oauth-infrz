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
     */
    public function mainAction()
    {
        if (!$this->authFactory->isAuthenticated()) {
            $currentURL = sprintf('https://%s%s', $_SERVER["SERVER_NAME"], $_SERVER["REQUEST_URI"]);
            $this->responseBuilder->buildLogin($currentURL);
        }
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
        if ($client->redirect_uri != urldecode($redirect_uri)) {
            $this->responseBuilder->buildError('invalid_param', 'The given redirect_uri is invalid.');
        }
        $page_token = $this->db->insertPageToken($this->authFactory->getUser());

        $this->responseBuilder->buildAuthorize($client, $page_token->token, $redirect_uri);
    }

    /**
     * Displaying information about the access grant and redirecting to client-site with code.
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

        $auth_code = $this->db->insertAuthCode($client, $user, $scope);
        if (!strpos($redirect_uri, '?')) {
            $redirect_uri = sprintf('%s?code=%s', $redirect_uri, $auth_code->code);
        } else {
            $redirect_uri = rtrim($redirect_uri, '&');
            $redirect_uri = sprintf('%s&code=%s', $redirect_uri, $auth_code->code);
        }

        $this->responseBuilder->buildAuthorizeGranted($client, $redirect_uri, $scope);
    }

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
            $auth_token = $this->db->insertAuthToken($client, $user, $auth_code->scope);
            $this->db->deleteAuthCode($auth_code->code);
        } elseif ($grant_type == 'refresh_token') {
            if (!$refresh_token = $this->db->getRefreshTokenByToken($code)) {
                $this->responseBuilder->buildJsonError('not_found');
            }
            if (!$auth_token = $this->db->getAuthTokenById($refresh_token->auth_token_id)) {
                $this->responseBuilder->buildJsonError('not_found');
            }
            if ($client->id != $auth_token->client_id) {
                $this->responseBuilder->buildJsonError('no_permission');
            }
            $user = $this->db->getuserbyid($auth_token->user_id);
            $auth_token_new = $this->db->insertAuthToken($client, $user, $auth_token->scope);
            $this->db->deleteRefreshToken($refresh_token->token);
            $this->db->deleteAuthToken($auth_token->token);
            $auth_token = $auth_token_new;
        } else {
            $this->responseBuilder->buildJsonError('invalid_param');
        }
        $refresh_token = $this->db->insertRefreshToken($auth_token);
        $this->responseBuilder->buildAuthToken($auth_token, $refresh_token);
    }
}
