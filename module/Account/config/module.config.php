<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Account;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Workmark\Invoice\Invoice;
use Workmark\Model\Model;
use Workmark\Model\Invoices;

return [
		'router' => [
				'routes' => [
						
						'account' => [
								'type'    => Segment::class,
								'options' => [
										'route' =>  '/account[/][:controller[/][:action][/]]',
										'defaults'  =>  [
												'__NAMESPACE__' =>  'Account\Controller',
												'controller' => Controller\IndexController::class,
												'action'     => 'index',
										],
								],
								'may_terminate' =>  true,
								'child_routes'  => [
										'wildcard'  =>  [
												'type'  =>  'Wildcard'
										],
								],
						],
						'login' => [
								'type'    => Segment::class,
								'options' => [
										'route' =>  '/account/log/in',
										'defaults'  =>  [
												'__NAMESPACE__' =>  'Account\Controller',
												'controller' => Controller\LogController::class,
												'action'     => 'in',
										],
								],
								
								'may_terminate' =>  true,
								'child_routes'  => [
										'wildcard'  =>  [
												'type'  =>  'Wildcard'
										],
								],
						],
						
				],
		],
		'controllers' => [
				'factories' => [
						
				],
				'aliases' =>  [
						'log'     =>  \Account\Controller\LogController::class,
						'teams'     =>  \Account\Controller\TeamsController::class,
				    'users'     =>  \Account\Controller\UsersController::class,
				    'officials'     =>  \Account\Controller\OfficialsController::class,
						'players'     =>  \Account\Controller\PlayersController::class,
						'scrimmages'     =>  \Account\Controller\ScrimmagesController::class,
						'availability'     =>  \Account\Controller\AvailabilityController::class,
				    
				]
		],
		'view_manager' => [
				'display_not_found_reason' => true,
				'display_exceptions'       => true,
				'doctype'                  => 'HTML5',
				'not_found_template'       => 'error/404',
				'exception_template'       => 'error/index',
				'template_map' => [
						'layout/loginLayout'           => __DIR__ . '/../view/layout/loginLayout.phtml',
						'layout/account'           => __DIR__ . '/../view/layout/accountLayout.phtml',
						'layout/terminalLayout'           => __DIR__ . '/../view/layout/terminalLayout.phtml',
						'account/index/index' => __DIR__ . '/../view/account/index/index.phtml',
						'error/404'               => __DIR__ . '/../view/error/404.phtml',
						'error/index'             => __DIR__ . '/../view/error/index.phtml',
				],
				'template_path_stack' => [
						__DIR__ . '/../view',
				],
				'strategies' => [
						'ViewJsonStrategy',
				],
		],
];
