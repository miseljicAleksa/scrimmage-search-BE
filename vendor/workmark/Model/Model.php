<?php
namespace Workmark\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

class Model
{
	/** @var Adapter **/
	private $adapter;
	private $models = array();
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
	}
	
	public function getAdapter()
	{
		return $this->adapter;
	}
	
	public function get($class)
	{
		if (!class_exists($class))
		{
			throw new \Exception("Model does not exists");
		}
		//$key = $this->clean($class);
		
		if (isset($this->models[$class]))
		{
			return $this->models[$class];
		}else{
			$tg = new TableGateway($class::$table, $this->adapter, null, new ResultSet());
			$model = new $class($tg, $this);
			$this->models[$class] = $model;
			return $model;
		}
	}
}
