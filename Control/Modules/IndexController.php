<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class IndexController extends AbstractController
{
    public function mainAction()
    {
        $this->responseBuilder->buildHome();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
