<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Workmark\Model\Timeline;
use Workmark\Controller\WorkmarkController;
use Application\Controller\IndexController;
use Application\Controller\ApiController;
use Workmark\User\User;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

class Module implements ConfigProviderInterface
{
    const VERSION = '3.0.3-dev';
    
    
    public function onBootstrap($e)
    {
    	$app = $e->getParam('application');
    	$serviceManager = $app->getServiceManager();
    	
    	// The following line instantiates the SessionManager and automatically
    	// makes the SessionManager the 'default' one.
    	$sessionManager = $serviceManager->get(SessionManager::class);
    	
    	
    	//$p = $e->getRouteMatch()->getParams();
    	//$controllerClass = $p['controller'];
    	
    	
    	//die(var_dump($controllerClass));
    	
    	
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
    					\Workmark\Model\Timeline::class => function($container) {
    						$tableGateway = $container->get(Workmark\Model\TimelineTableGateway::class);
    						return new \Workmark\Model\Timeline($tableGateway);
    					},
    					Workmark\Model\TimelineTableGateway::class => function ($container) {
    						$dbAdapter = $container->get(AdapterInterface::class);
    						$resultSetPrototype = new ResultSet();
    						//$resultSetPrototype->setArrayObjectPrototype(new Workmark\Model\Entity\Timeline());
    						return new TableGateway('projects', $dbAdapter, null, $resultSetPrototype);
    					},
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
    					Controller\ApiController::class => function($container) {
    						return new ApiController($container);
    					},
    					],
    			];
    }
}
