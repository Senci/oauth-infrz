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
        $this->isGetRequest();

        // set all needed GET-Variables to ${get-variable-name} if set
        $client_id     = isset($_GET['client_id'])     ? $_GET['client_id'] : false;
        $redirect_uri  = isset($_GET['redirect_uri'])  ? $_GET['redirect_uri'] : false;
        $scope         = isset($_GET['scope'])         ? $_GET['scope'] : false;


        if (!$client_id or !$redirect_uri) {
            $this->responseBuilder->buildError('missing_param');
        }

        // set scope to array or default value
        if ($scope) {
            $scope = explode(',', $scope);
        } else {
            $scope = array();
        }

        $client = $this->db->getClientByClientId($client_id);

        if (!$client) {
            $this->responseBuilder->buildError('invalid_param', 'The given client_id is invalid.');
        }
        if ($client->redirect_uri != urldecode($redirect_uri)) {
            $this->responseBuilder->buildError('invalid_param', 'The given redirect_uri is invalid.');
        }

        if ($this->authFactory->isAuthenticated()) {
            $this->responseBuilder->buildAuthorize($client, $scope);
        } else {
            $this->responseBuilder->buildLogin();
        }
    }

    /**
     * Displaying information about the access grant and redirecting to client-site with code.
     */
    public function grantAction()
    {
        $this->isPostRequest();

        //TODO implement me!
    }
}
