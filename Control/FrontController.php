<?php

namespace Infrz\OAuth\Control;

use Infrz\OAuth\ResponseBuilder;
use Infrz\OAuth\Control\Actions\Action;

class FrontController
{
    /* path to root dir */
    protected $root;
    /* initial action */
    protected $mainAction = 'index_Home';
    protected $responseBuilder;
    protected $request;

    /**
     * @param string $rootPath path to root directory
     */
    public function __construct($rootPath)
    {
        $this->root = $rootPath;
        $this->responseBuilder = new ResponseBuilder();
        $this->request = array_merge($_GET, $_POST);
        //$this->request['method'] = $_SERVER['REQUEST_METHOD'];
        // TODO load autoloader packages
    }

    /**
     * Gets executed on every request.
     */
    public function run()
    {
        $actionCommand = isset($request['scope']) ? $request['scope'] : false;

        if ($actionCommand) {
            $this->execAction($actionCommand);
        } else {
            $this->execAction($this->mainAction);
        }
    }

    /**
     * Executes an Action
     *
     * @param string $actionCommand
     */
    private function execAction($actionCommand)
    {
        $moduleName = $this->getModuleName($actionCommand);
        $actionName = sprintf('%sAction', $this->getActionName($actionCommand));

        // check for module existence
        $modulePath = sprintf('%s/Control/%s', $this->root, $moduleName);
        if (!is_dir($modulePath)) {
            $this->responseBuilder->buildError('not_found');
        }

        // check for action existence
        $actionSource = sprintf('%s/%s.php', $modulePath, $actionName);
        if (!is_file($actionSource)) {
            $this->responseBuilder->buildError('not_found');
        }

        // include action
        require_once($actionSource);

        $str = 'Infrz\OAuth\ResponseBuilder';
        var_dump(class_exists($str));

        exit();
        /* @var Action $action */
//        $action = new IndexAction($this->root);
        $action = new $actionName($this->root);
        if (!is_object($actionName)) {
            $this->responseBuilder->buildError('internal_server_error');
        }

        /* @var Action $action */
        $action->run();
    }

    /**
     * Extracts the action name from an action command
     *
     * @param string $actionCommand
     * @return string action name
     */
    protected function getActionName($actionCommand)
    {
        return substr($actionCommand, strrpos($actionCommand, "_") + 1);
    }

    /**
     * Extracts the module name from an action command
     *
     * @param string $actionCommand
     * @return string module name
     */
    protected function getModuleName($actionCommand)
    {
        return substr($actionCommand, 0, strrpos($actionCommand, "_"));
    }
}
