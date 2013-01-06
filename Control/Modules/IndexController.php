<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class IndexController extends AbstractController
{
    public function mainAction()
    {
        $this->response_builder->buildLogin('http://www.google.de/');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
