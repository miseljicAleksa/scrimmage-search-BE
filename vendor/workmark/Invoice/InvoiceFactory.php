<?php
namespace Workmark\Invoice;
use Interop\Container\ContainerInterface;
use Workmark\User\AuthAdapter;
use Zend\ServiceManager\Factory\FactoryInterface;
use Workmark\Model\Model;
use Workmark\Model\Invoices;
use Workmark\Invoice\Invoice;
/**
 * This is the factory class for AuthAdapter service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class InvoiceFactory implements FactoryInterface
{
	/**
	 * This method creates the AuthAdapter service and returns its instance.
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new Invoice($container->get(Model::class)->get(Invoices::class));
	}
}