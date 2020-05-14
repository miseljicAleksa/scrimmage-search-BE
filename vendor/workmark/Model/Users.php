<?php
namespace Workmark\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;
use Workmark\User\User;

class Users extends ModelAbstract
{
	static $table = 'users';
	
	public function __construct(TableGatewayInterface $tableGateway, Model $model)
	{
		$this->tableGateway = $tableGateway;
		$this->model = $model;
	}
	
	public function fetchAll()
	{
		return $this->tableGateway->select();
	}
	
	public function getByEmail($email)
	{
		$user = $this->getUsers(['email'=>$email], true);
		if (!isset($user[0]) || !is_array($user[0]))
		{
			throw new \Exception("Can't find user by the email: ".$email);
		}
		return $user[0];
	}
	
	public function getById($id)
	{
		$user = $this->getUsers(['id'=>$id]);
		return $user[0];
	}
	
	public function getByFbId($fbId)
	{
		if(strlen($fbId)<5)
		{
			return array();
		}
		$user = $this->getUsers(['fbId'=>$fbId]);
		if (!isset($user[0]) || !is_array($user[0]))
		{
			return array();
		}
		return $user[0];
	}
	
	public function getByGoogleId($ggId)
	{
		$user = $this->getUsers(['ggId'=>$ggId]);
		return $user[0];
	}
	
	
	/**
	 * getUsers
	 *
	 * @param array $args
	 * @return Users
	 */
	public function getTotal(array $args = null)
	{
		$sqlSelect = "SELECT count(*) as num FROM users ";
		if(isset($args['q']) && $args['q'] != false)
		{
			$args['q'] = addcslashes($args['q'], "\000\n\r\\'\"\032");
			$sqlSelect .= "WHERE (email LIKE '{$args['q']}%' OR first_name LIKE '{$args['q']}%') ";
		}
		//die($sqlSelect);
		$result = $this->query($sqlSelect, []);
		return $result[0]['num'];
	}
	
	/**
	 * getUsers
	 *
	 * @param array $args
	 * @return Users
	 */
	public function getUsers(array $args = null, $pass = false)
	{
		$passSql = $pass ? ", u.password" : "";
		$sqlSelect = "SELECT u.id, u.first_name as first_name, u.last_name as last_name, u.email".$passSql.", u.properties, u.status, u.created_on, u.fbId, u.role FROM `users` u
		
		WHERE 1 ";
		if(isset($args['id']))
		{
			$sqlSelect .= "AND u.id = :id ";
		}
		if(isset($args['email']))
		{
			$sqlSelect .= "AND u.email = :email ";
		}
		if(isset($args['fbId']))
		{
			$sqlSelect .= "AND u.fbId = :fbId ";
		}
		if(isset($args['ggId']))
		{
			$sqlSelect .= "AND u.ggId = :ggId ";
		}
		if(isset($args['q']) && $args['q'] != false)
		{
			$args['q'] = addcslashes($args['q'], "\000\n\r\\'\"\032");
			$sqlSelect .= "AND (u.email LIKE '{$args['q']}%' OR u.first_name LIKE '{$args['q']}%') ";
		}
		if(isset($args['desc']))
		{
			$sqlSelect .= "ORDER by id desc ";
		}
		if(isset($args['startFetch']) && isset($args['perPage']))
		{
			$sqlSelect .= "limit ".$args['startFetch'].", ".$args['perPage']." ";
		}
		
		$result = $this->query($sqlSelect, $args);
		
		
		
		return $result;
	}
		
	/**
	* getUsersStats
	*
	* @param array $args
	* @return User
	*/
	public function getUsersStats(array $args = array())
	{
		
		$sqlSelect = "SELECT
		u.first_name, u.last_name, u.email, u.fbId,
		COUNT(DISTINCT t.id) as tasksCount,
        COUNT(DISTINCT it.taskId) as tasksUnDone,
        COUNT(DISTINCT i.id) as itemsCount,
        COUNT(DISTINCT it.id) as itemsUnDone,
        COUNT(DISTINCT a.id) as activitiesCount
				
		FROM `users` u
		LEFT JOIN tasks t ON t.userId = u.id
		LEFT JOIN items it ON t.id = it.taskId AND it.done = 0
		LEFT JOIN items i ON t.id = i.taskId
		LEFT JOIN activities a ON u.id = a.userId
		LEFT JOIN projects p ON t.projectId = p.id
				WHERE 1
				AND t.startDate >= DATE_SUB(NOW(),INTERVAL 1 YEAR) ";
		
		if(isset($args['organisationId']))
		{
			$sqlSelect .= "AND p.organisationId = :organisationId
			AND p.status = 'active' ";
		}
		
		$sqlSelect .= "GROUP BY u.email ";
		
		
		//$sqlSelect .= "GROUP BY YEAR(t.startDtae), MONTH(t.startDtae)";
		
		$result = $this->query($sqlSelect, $args);
		
		return $result;
	}
	
	public function save($data)
	{
		//die(var_dump($data));
		
		if (isset($data['email']))
		{
			if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
			{
				throw new \Exception("Invalid email address");
			}
		}
		
		//Set default values
		$values['id'] = $data['id'] ?? 0;
		$values['email'] = $data['email'] ?? null;
		$values['password'] = $data['password'] ?? null;
		$values['first_name'] = $data['first_name'] ?? null;
		$values['last_name'] = $data['last_name'] ?? null;
		$values['bio'] = $data['bio'] ?? null;
		$values['fbId'] = $data['fbId'] ?? null;
		$values['last_login'] = $data['last_login'] ?? null;
		$values['status'] = $data['status'] ?? null;
		$values['properties'] = $data['properties'] ?? null;
		$values['created_on'] = $data['created_on'] ?? null;
		$values['role'] = $data['role'] ?? null;
		
		return $this->insertOrUpdate($values, $data);
	}
	
	public function delete($id)
	{
		$this->tableGateway->delete(['id' => (int) $id]);
	}
}
