<?php
namespace SEI\Models;

use SEI\DbAdapters\DbAdapterInterface;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;


class Messages extends ModelAbstract implements ModelInterface
{
	static $table = 'messages';
	public $model;
	
	
	/**
	 * @return string
	 */
	public function getModel() {
	    return $this->model;
	}
	
	
	/**
	 * Constructor.
	 */

	public function __construct(DbAdapterInterface $dbAdapter)
	{
	    $dbAdapter->setDbTable(self::getTablePrefix()."messages");
	    $this->setDbAdapter($dbAdapter);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Workmark\Model\ModelAbstract::getById()
	 */
	public function getById($id)
	{
	    $team = $this->getTeams(['id'=>$id]);
	    return $team[0];
	}
	
	public function getAllMessages(array $args = null) {
	    $sQuery = "SELECT m.*, CONCAT(u.first_name, ' ',  u.last_name) as name
				FROM ".$this->getDbAdapter()->getDbTable()." m
				JOIN users u on u.id = m.user_from
				WHERE 1 ";
	    
	    
	    if(isset($args['id']))
	    {
	    	$sQuery .= 'AND m.id = ' . intval($args['id']) . ' ';
	    }
	    
	    if(isset($args['request_id']))
	    {
	    	$sQuery .= 'AND m.request_id = ' . intval($args['request_id']) . ' ';
	    }
	    
	    return $this->getDbAdapter()->query($sQuery);
	}
	

	
	
	
	/**
	 * @return string
	 */
	public function getMyData() {
	    $row = $this->getMapper();
	    unset($row['password']);
	    return $row;
	}
	

	
	/**
	 * @return string
	 */
	
	public function fetchAll()
	{
		return $this->tableGateway->select();
	}
		
	
	public function save($data)
	{
		
		//Set default values
		$values['request_id'] = $data['request_id'] ?? null;
		$values['user_from'] = $data['user_from'] ?? null;
		$values['created_on'] = $data['created_on'] ?? null;
		$values['message'] = $data['message'] ?? null;
		$values['viewed'] = $data['viewed'] ?? null;
		
		return $this->getDbAdapter()->insert($data);
	}
	
	public function delete($id)
	{
		$this->tableGateway->delete(['id' => (int) $id]);
	}
}
