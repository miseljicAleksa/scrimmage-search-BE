<?php
namespace Workmark\User;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use Zend\Authentication\Storage\Session as SessionStorage;
use Workmark\User\AuthAdapter;

/**
 * The factory responsible for creating of authentication service.
 */
class AuthenticationServiceFactory implements FactoryInterface
{
	/**
	 * This method creates the Zend\Authentication\AuthenticationService service
	 * and returns its instance.
	 */
	public function __invoke(ContainerInterface $container,
			$requestedName, array $options = null)
	{
		/* @var $sessionManager SessionManager */
		$sessionManager = $container->get(SessionManager::class);
		try{
			$authStorage = new SessionStorage('Zend_Auth', 'session', $sessionManager);
		}catch (\Exception $e)
		{
			$sessionManager->regenerateId(true);
			$authStorage = new SessionStorage('Zend_Auth', 'session', $sessionManager);
		}
		$authAdapter = $container->get(AuthAdapter::class);
		
		// Create the service and inject dependencies into its constructor.
		return new AuthenticationService($authStorage, $authAdapter);
	}
}
