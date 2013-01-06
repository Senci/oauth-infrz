<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class ClientController extends AbstractController
{
    public function mainAction()
    {
        $this->response_builder->buildError('not_found');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
