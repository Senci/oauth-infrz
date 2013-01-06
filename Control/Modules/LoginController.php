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
        $this->response_builder->buildLogin('http://www.google.de/');
    }

    public function authorizeAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->response_builder->buildError('not_found');
        }

        $username     = isset($_POST['username']) ? $_POST['username'] : false;
        $password     = isset($_POST['password']) ? $_POST['password'] : false;

        $this->response_builder->buildLogin('http://www.google.de/');
    }
}
