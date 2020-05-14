<?php
namespace Workmark\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

class ModelAbstract
{
	
	/**@var TableGateway **/
	protected $tableGateway;
	/**@var Model **/
	public $model;
	
	public function query($sqlSelect, $bind, $update = false)
	{
		$bind = $this->stripHtml($bind);
		
		$result = $this->tableGateway->getAdapter()->query($sqlSelect, $bind, new ResultSet());
		
		return $update?$result:$result->toArray();
	}
	
	public function getById($id)
	{
		$rowset = $this->tableGateway->select(array('id' => intval($id)));
		return (array)$rowset->current();
	}
	
	public function insertOrUpdate(array $values, array $incomingData)
	{
		$values = $this->stripHtml($values);
		
		if (((int)$values['id']) > 0) {
			
			//UPDATE ONLY ONE FIELD
			foreach ($values as $key => $value)
			{
				if(!isset($incomingData[$key]))
				{
					unset($values[$key]);
				}
			}
			$this->tableGateway->update($values, ['id' => $values['id']]);
			return $values['id'];
		}else{
			$this->tableGateway->insert($values);
			return $this->tableGateway->lastInsertValue;
		}
	}
	
	private function stripHtml($array)
	{
		//return $array;
		
		$newArray = array_combine(array_keys($array), array_map(function($v){
			return $v?trim(strip_tags($v)):$v;
		}, $array));
		
		return $newArray;
	}
}
