<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;
use Infrz\OAuth\Model\User;

class LoginController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function mainAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->responseBuilder->buildError('not_found');
        }

        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '%2F';

        $this->responseBuilder->buildLogin($redirect);
    }

    /**
     * Authorizes the user with given username nad password.
     */
    public function authorizeAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->responseBuilder->buildError('not_found');
        }

        $username     = isset($_POST['username']) ? $_POST['username'] : false;
        $password     = isset($_POST['password']) ? $_POST['password'] : false;
        $redirect     = isset($_POST['redirect']) ? $_POST['redirect'] : '%2F';

        $user = $this->authFactory->signIn($username, $password);
        if (!$user instanceof User) {
            $this->responseBuilder->buildLogin($redirect, 'invalid_credentials');
        }

        $this->responseBuilder->addTwigGlobals();
        $this->responseBuilder->buildLoginSuccess(urldecode($redirect), $user);

    }
}
