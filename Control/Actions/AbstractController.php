<?php

namespace Infrz\OAuth\Control\Actions;

abstract class Action
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
