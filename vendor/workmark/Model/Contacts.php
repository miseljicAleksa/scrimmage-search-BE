<?php
namespace Workmark\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\ResultSet\ResultSet;

class Contacts extends ModelAbstract
{
	static $table = 'contacts';
	
	public function __construct(TableGatewayInterface $tableGateway, Model $model)
	{
		$this->tableGateway = $tableGateway;
		$this->model = $model;
	}
	
	public function fetchAll()
	{
		return $this->tableGateway->select();
	}
	
	/**
	 * getContacts
	 * 
	 * @param array $args
	 * @return unknown
	 */
	public function getContacts(array $args)
	{
		$sqlSelect = "SELECT c.sets, c.created_on, u.first_name, u.last_name, u.fbId, u.email, u.id, c.id as contact_id
		FROM `contacts` c
		JOIN `users` u ON (c.sender = u.id)
		WHERE 1 ";
		
		if(isset($args['receiver']))
		{
			$sqlSelect .= "AND c.receiver = :receiver ";
		}
		if(isset($args['sender']))
		{
			$sqlSelect .= "AND c.sender = :sender ";
		}
		if(isset($args['search_text']))
		{
			$args['search_text'] = addcslashes($args['search_text'], "\000\n\r\\'\"\032");
			$sqlSelect .= "AND u.email LIKE '{$args['search_text']}%' ";
		}
		$sqlSelect .= "GROUP BY u.id ORDER BY created_on DESC";
		$result = $this->query($sqlSelect, $args);
		
		return $result;
	}
	
	
	/**
	 * getContacts
	 *
	 * @param array $args
	 * @return unknown
	 */
	public function searchContacts(array $args)
	{
		$sqlSelect = "SELECT u.first_name, u.last_name, u.fbId, u.email, u.id
		FROM `users` u 
		WHERE 1 ";
		
		if(isset($args['search_text']))
		{
			$args['user_id'] = intval($args['user_id']);
			$args['search_text'] = addcslashes($args['search_text'], "\000\n\r\\'\"\032");
			$sqlSelect .= "AND (u.email LIKE '{$args['search_text']}%' OR u.first_name LIKE '{$args['search_text']}%') 
			AND u.id != '{$args['user_id']}' ";
		}
		
		$sqlSelect .= "ORDER BY created_on DESC";
		$result = $this->query($sqlSelect, $args);
		
		return $result;
	}
	
	/**
	 * save
	 * 
	 * @param unknown $data
	 * @return unknown
	 */
	public function save($data)
	{
		$values['id'] = $data['id'] ?? 0;
		$values['sender'] = $data['sender'] ?? NULL;
		$values['receiver'] = $data['receiver'] ?? NULL;
		$values['sets'] = $data['sets'] ?? NULL;
		$values['created_on'] = $data['created_on'] ?? NULL;
		
		return $this->insertOrUpdate($values, $data);
	}
	
	
	public function delete($id)
	{
		return $this->tableGateway->delete(['id' => (int) $id]);
	}
}
