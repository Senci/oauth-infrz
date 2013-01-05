<?php

namespace Infrz\OAuth\Control;

use Infrz\OAuth\View\ResponseBuilder;
use Infrz\OAuth\Control\Modules\AbstractController;

class FrontController
{
    /* path to root dir */
    protected $root;
    /* initial action */
    protected $mainAction = 'Index_home';
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
        $moduleName = sprintf('%sController', $this->getModuleName($actionCommand));
        $actionName = sprintf('%sAction', $this->getActionName($actionCommand));

        // check for module existence
        $modulePath = sprintf('%s/Control/Modules/%s.php', $this->root, $moduleName);
        if (!is_file($modulePath)) {
            $this->responseBuilder->buildError('not_found');
        }

        // include action
        require_once($modulePath);

        $className = sprintf('Infrz\OAuth\Control\Modules\%s', $moduleName);
        /* @var AbstractController $controller */
        $controller = new $className($this->root);

        // check whether the Controller got initialized correctly
        if (!is_object($controller)) {
            $this->responseBuilder->buildError('internal_server_error');
        }

        // check for action existence
        if (!method_exists($controller, $actionName)) {
            $this->responseBuilder->buildError('not_found');
        }

        $controller->$actionName();
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
