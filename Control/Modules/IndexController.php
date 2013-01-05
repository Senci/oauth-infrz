<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class IndexController extends AbstractController
{

    public function homeAction()
    {
        exit('home action! hell yea!');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
