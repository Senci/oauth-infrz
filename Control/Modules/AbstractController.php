<?php

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\View\ResponseBuilder;
use Infrz\OAuth\Model\DatabaseWrapper;

abstract class AbstractController
{
    protected $response_builder;
    protected $database;

    public function __construct()
    {
        $this->response_builder = new ResponseBuilder();
        $this->database = new DatabaseWrapper();
    }

    /**
     * The main action. Gets executed on "/{module-name}" call
     */
    abstract public function mainAction();
}
