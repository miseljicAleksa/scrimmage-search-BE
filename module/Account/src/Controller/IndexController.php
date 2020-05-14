<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Account\Controller;

use Zend\View\Model\ViewModel;
use Workmark\Controller\WorkmarkController;
use Interop\Container\ContainerInterface;

class IndexController  extends WorkmarkController
{
	
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
	}
	
    public function indexAction()
    {
    	return new ViewModel();
    }
}
