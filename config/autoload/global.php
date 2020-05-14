<?php
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Workmark\Invoice\Invoice;
use Workmark\Model\Invoices;
use Workmark\Model\Model;
use Workmark\User\InvoiceFactory;

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
// Session configuration.
		'session_config' => [
				// Session cookie will expire in 30 days.
				'cookie_lifetime' => 60*60*24*30,
				'remember_me_seconds' => 60*60*24*30,
				'name'                => 'scrimmag_db',
				//'save_path'           => 'data/sess/',
				// Session data will be stored on server maximum for 30 days.
				'gc_maxlifetime'     => 60*60*24*30,
		],
		// Session manager configuration.
		'session_manager' => [
				// Session validators (used for security).
				'validators' => [
						//RemoteAddr::class,
						HttpUserAgent::class,
				]
		],
		// Session storage configuration.
		'session_storage' => [
				'type' => SessionArrayStorage::class
		],
		'module_layouts' => [
				'Login' => 'layout/loginLayout',
				'Account' => 'layout/accountLayout',
				'Application' => 'layout/layout',
				'Terminal' => 'layout/terminal',
		],
    'db' => [
        'driver' => 'Pdo',
    	'dsn'    => 'mysql:dbname=scrimmag_db;host=localhost;charset=utf8mb4;collation=utf8mb4_unicode_ci',
    	'username' => 'scrimmag_usr',
    	'password' => 'I%gV?N$FA$9D',
    ],	
    'service_manager' => [
   		'factories' => [
   			'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
   				\Workmark\Invoice\Invoice::class => \Workmark\Invoice\InvoiceFactory::class
   		],
	],
];
