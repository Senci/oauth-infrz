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

        $access_token = isset($_GET['access_token']) ? urldecode($_GET['access_token']) : false;

        if (!$access_token) {
            $this->responseBuilder->buildJsonError('missing_param');
        }
        if (!$access_token = $this->db->getAccessTokenByToken($access_token)) {
            $this->responseBuilder->buildJsonError('not_found');
        }
        if (!$user = $this->db->getUserById($access_token->user_id)) {
            $this->responseBuilder->buildJsonError('not_found');
        }
        if ($access_token->user_id != $user->id) {
            $this->responseBuilder->buildJsonError('no_permission');
        }

        $this->responseBuilder->buildUser($user, $access_token->scope);
    }
}
