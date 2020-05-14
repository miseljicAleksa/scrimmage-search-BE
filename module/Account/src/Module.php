<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Account;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Workmark\Model\Timeline;
use Workmark\Controller\WorkmarkController;
use Account\Controller\IndexController;
use Account\Controller\LogController;
use Zend\Mvc\MvcEvent;
use Account\Controller\QuestionsController;
use Account\Controller\UsersController;
use Zend\Session\SessionManager;
use Workmark\User\AuthManager;
use Workmark\User\User;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Account\Controller\TeamsController;
use Account\Controller\OfficialsController;
use Account\Controller\PlayersController;
use Workmark\Model\Teams;
use Account\Controller\ScrimmagesController;
use Account\Controller\AvailabilityController;

class Module implements ConfigProviderInterface
{
	const VERSION = '3.0.3-dev';
	public function onBootstrap(MvcEvent $e)
	{
		$app = $e->getParam('application');
		
		
		$eventManager = $app->getEventManager();
		$sharedEventManager = $eventManager->getSharedManager();
		
		// Register a dispatch event
		$sharedEventManager->attach(AbstractActionController::class,
				MvcEvent::EVENT_DISPATCH, array($this, 'userIsLogged'), 100);
		$sharedEventManager->attach(AbstractActionController::class,
				MvcEvent::EVENT_DISPATCH, array($this, 'changeLayout'));
	}
	
	public function userIsLogged(MvcEvent $event)
	{
		
		//$sessionManager = $event->getApplication()->getServiceManager()->get(SessionManager::class);
		
		$authService = $event->getApplication()->getServiceManager()->get(AuthenticationService::class);
		$user = $event->getApplication()->getServiceManager()->get(User::class);
		$contr = $event->getTarget();
		$p = $event->getRouteMatch()->getParams();
		$controllerClass = $p['controller'];
		$moduleName = substr($p['__NAMESPACE__'], 0, strpos($p['__NAMESPACE__'], '\\'));
		
		if ($authService->getIdentity() != null) {
			$user->init($authService->getIdentity());
			User::setCurrent($user);
		}
		
		if($controllerClass == "log")
		{
			$controllerClass = "Account\Controller\LogController";
		}
		
		//die(var_dump($p));
		
		if($moduleName == 'Account' && !User::getCurrent() && $controllerClass != "Account\Controller\ApiController")
		{
			//return $contr->redirect()->toUrl('/account/log/in');
		}
		
		if($moduleName == 'Account' && (!User::getCurrent() || !User::getCurrent()->isManager()) && $controllerClass != "Account\Controller\LogController")
		{
			return $contr->redirect()->toUrl('/account/log/in');
		}
		
	}
	
	public function changeLayout(MvcEvent $e)
	{
		$controller = get_class($e->getTarget());
		
		$moduleName = substr($controller, 0, strpos($controller, '\\'));
		$config     = $e->getApplication()->getServiceManager()->get('config');
		//die(var_dump($controller));
		if(isset($config['module_layouts'][$moduleName])){
			$e->getTarget()->layout($config['module_layouts'][$moduleName]);
		}
		if($controller == "Account\Controller\LogController"){
			$e->getTarget()->layout("layout/loginLayout");
		}
		$routeMatch = $e->getRouteMatch();
		$p = $routeMatch->getParams();
		//die(var_dump($e->getTarget()->params()->fromRoute()));
		if(($p["controller"] == 'invoicing' && $p["action"] == 'invoice') ||
			($p["controller"] == 'invoicing' && $p["action"] == 'offer'))
		{
			$e->getTarget()->layout("layout/terminalLayout");
		}
		if ($routeMatch) {
			$e->getApplication()->getMvcEvent()->getViewModel()
			->setVariables($routeMatch->getParams());
		}
	}
	public function getConfig()
	{
		return include __DIR__ . '/../config/module.config.php';
	}
	
	public function getServiceConfig()
	{
		return [
				'factories' => [
						\Workmark\Model\Model::class => function($container) {
							return new \Workmark\Model\Model($container->get(AdapterInterface::class));
						},
						\Workmark\User\User::class => function($container) {
							$model = new \Workmark\Model\Model($container->get(AdapterInterface::class));
							return new User($model->get(\Workmark\Model\Users::class));
						},
						\Workmark\User\AuthAdapter::class => \Workmark\User\AuthAdapterFactory::class,
						\Zend\Authentication\AuthenticationService::class => \Workmark\User\AuthenticationServiceFactory::class,
						\Workmark\User\AuthManager::class => \Workmark\User\AuthManagerFactory::class,
						],
					];
	}
	
	
	
	public function getControllerConfig()
	{
		return [
				'factories' => [
						Controller\IndexController::class => function($container) {
							return new IndexController($container);
						},
						Controller\LogController::class => function($container) {
							$authManager = $container->get(\Workmark\User\AuthManager::class);
							return new LogController($container, $authManager);
						},
						Controller\TeamsController::class => function($container) {
							return new TeamsController($container);
						},
						Controller\UsersController::class => function($container) {
							return new UsersController($container);
						},
						Controller\OfficialsController::class => function($container) {
						    return new OfficialsController($container);
						},
						Controller\PlayersController::class => function($container) {
						    return new PlayersController($container);
						},
						Controller\ScrimmagesController::class => function($container) {
							return new ScrimmagesController($container);
						},
						Controller\AvailabilityController::class => function($container) {
							return new AvailabilityController($container);
						},
					],
				];
	}
}
