<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class AuthorizeController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function mainAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->responseBuilder->buildError('not_found');
        }

        // set all needed GET-Variables to ${get-variable-name} if set
        $response_type = isset($_GET['response_type']) ? $_GET['response_type'] : false;
        $client_id     = isset($_GET['client_id'])     ? $_GET['client_id'] : false;
        $redirect_uri  = isset($_GET['redirect_uri'])  ? $_GET['redirect_uri'] : false;
        $scope         = isset($_GET['scope'])         ? $_GET['scope'] : false;
        $state         = isset($_GET['state'])         ? $_GET['state'] : false;


        if (!$response_type or !$client_id or !$redirect_uri) {
            $this->responseBuilder->buildError('missing_param');
        }

        // set scope to array or default value
        if ($scope) {
            $scope = explode(',', $scope);
        } else {
            $scope = array('username', 'first_name', 'last_name');
        }

        $client = $this->database->getClientById($client_id);

        if (!$client) {
            $this->responseBuilder->buildError('invalid_param', 'The given client_id is invalid.');
        }
        if ($client->redirect_uri != urldecode($redirect_uri)) {
            $this->responseBuilder->buildError('invalid_param', 'The given redirect_uri is invalid.');
        }

        if ($this->isAuthorized()) {
            $this->responseBuilder->buildAuthorize($client, $scope);
        } else {
            $this->responseBuilder->buildLogin();
        }
    }

    public function grantAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->responseBuilder->buildError('not_found');
        }
    }

    protected function isAuthorized()
    {
        return true;
    }
}
