<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class LoginController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function mainAction()
    {
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
        if (!$user) {
            $this->responseBuilder->buildLogin('http://www.google.de/', 'invalid_credentials');
        }

        $this->responseBuilder->buildLoginSuccess(urldecode($redirect), $user);

    }
}
