<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            
            'application' => [
                'type'    => Segment::class,
                'options' => [
                		'route' =>  '/application[/:controller/:action]',
                		'defaults'  =>  [
                		'__NAMESPACE__' =>  'Application\Controller',
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
            'api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/api/:apiAction',
                    'constraints' => [
                        'apiAction' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        '__NAMESPACE__' =>  'Application\Controller',
                        'controller' => Controller\ApiController::class,
                        'action' => 'json',
                    ],
                    
                ],
            ],
        		'home' => [
        				'type' => Literal::class,
        				'options' => [
        						'route'    => '/',
        						'defaults'  =>  [
        								'__NAMESPACE__' =>  'Application\Controller',
        								'controller' => Controller\IndexController::class,
        								'action'     => 'index',
        						],
        				],
        		],
        		'privacy' => [
        				'type' => Literal::class,
        				'options' => [
        						'route'    => '/privacy',
        						'defaults'  =>  [
        								'__NAMESPACE__' =>  'Application\Controller',
        								'controller' => Controller\IndexController::class,
        								'action'     => 'privacy',
        						],
        				],
        		],
        		'terms' => [
        				'type' => Literal::class,
        				'options' => [
        						'route'    => '/terms',
        						'defaults'  =>  [
        								'__NAMESPACE__' =>  'Application\Controller',
        								'controller' => Controller\IndexController::class,
        								'action'     => 'terms',
        						],
        				],
        		],
        ],
    ],
		'controllers' => [
	        'factories' => [
	        ],
    		'aliases' =>  [
    				'api'     =>  \Application\Controller\ApiController::class,
    				'index'     =>  \Application\Controller\IndexController::class,
    		]
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/application'      => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
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
