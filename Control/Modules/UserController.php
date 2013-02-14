<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class UserController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function mainAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->responseBuilder->buildJsonError('not_found');
        }

        $oauth_token = isset($_GET['oauth_token']) ? urldecode($_GET['oauth_token']) : false;

        if (!$oauth_token) {
            $this->responseBuilder->buildJsonError('missing_param');
        }
        if (!$auth_token = $this->db->getAuthTokenByToken($oauth_token)) {
            $this->responseBuilder->buildJsonError('not_found');
        }
        if (!$user = $this->db->getUserById($auth_token->user_id)) {
            $this->responseBuilder->buildJsonError('not_found');
        }
        if ($auth_token->user_id != $user->id) {
            $this->responseBuilder->buildJsonError('no_permission');
        }

        $this->responseBuilder->buildUser($user, $auth_token->scope);
    }
}
