<?php

namespace Infrz\OAuth\Control\Modules;

abstract class AbstractController
{
    /* path to root dir */
    protected $root;

    public function __construct($rootDir)
    {
        $this->root = $rootDir;
    }

    /**
     * This is where the magic happens.
     *
     * @return mixed
     */
    abstract public function run();
}
