<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class IndexController extends AbstractController
{
    public function mainAction()
    {
        $this->responseBuilder->buildLogin('http://www.google.de/');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
