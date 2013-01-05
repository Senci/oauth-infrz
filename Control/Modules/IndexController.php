<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class IndexController extends AbstractController
{

    public function homeAction()
    {
        var_dump($_REQUEST['action']);
        exit('home action! hell yea!');
    }

    public function birtheAction()
    {
        exit('BIRTHE IS TOLL... action! hell yea!');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
