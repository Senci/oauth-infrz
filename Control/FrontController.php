<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control;

use Infrz\OAuth\View\ResponseBuilder;
use Infrz\OAuth\Control\Modules\AbstractController;

class FrontController
{
    /* initial action */
    protected $mainAction = 'index_main';
    protected $responseBuilder;
    protected $request;
    /* AuthFactoryInterface */
    protected $authFactory;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        if (!$config) {
            exit('Your config file is not set properly, please correct it and try again.');
        }
        $this->config = $config;
        if (!$authFactoryConfig = json_decode($config['auth_factory_config'])) {
            exit('The "auth_factory_config" variable in your config is not parsable by json_decode().');
        }

        $authFactoryClassName = sprintf('Infrz\OAuth\Control\Security\%s', $config['auth_factory']);
        $this->authFactory = new $authFactoryClassName($authFactoryConfig);
        $this->responseBuilder = new ResponseBuilder($this->authFactory, $config);
        $this->request = array_merge($_GET, $_POST);
    }

    /**
     * Gets executed on every request.
     */
    public function run()
    {
        $actionCommand = isset($this->request['action']) ? $this->request['action'] : false;

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
        $modulePath = sprintf('%s/Control/Modules/%s.php', getcwd(), $moduleName);
        if (!is_file($modulePath)) {
            $this->responseBuilder->buildError('not_found');
        }

        // include action
        require_once($modulePath);

        $className = sprintf('Infrz\OAuth\Control\Modules\%s', $moduleName);
        /* @var AbstractController $controller */
        $controller = new $className($this->config, $this->authFactory);

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
        return lcfirst(substr($actionCommand, strrpos($actionCommand, "_") + 1));
    }

    /**
     * Extracts the module name from an action command
     *
     * @param string $actionCommand
     * @return string module name
     */
    protected function getModuleName($actionCommand)
    {
        return ucfirst(substr($actionCommand, 0, strrpos($actionCommand, "_")));
    }
}
