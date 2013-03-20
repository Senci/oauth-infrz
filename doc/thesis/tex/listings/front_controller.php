private function execAction($actionCommand)
{
	// extract module name from action command
	$moduleName = $this->getModuleName($actionCommand) . 'Controller';
	// extract action name from action command
	$actionName = $this->getActionName($actionCommand) . 'Action';

	// generate module path & load module
	$modulePath = getcwd() . '/Control/Modules/%s.php' . $moduleName;
	require_once($modulePath);

	// generate class name with Namespace
	$className = 'Infrz\OAuth\Control\Modules\\' . $moduleName;
	
	// create instance of class with the class name $className
	/* @var AbstractController $controller */
	$controller = new $className($this->config, $this->authFactory);

	// run action $actionName
	$controller->$actionName();
}
