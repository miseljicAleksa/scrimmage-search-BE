<?php
namespace Workmark\Model;

use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;


class Requests extends ModelAbstract
{
	static $table = 'requests';
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

	public function __construct(TableGatewayInterface $tableGateway, $model)
	{
	    $this->tableGateway = $tableGateway;
	    $this->model = $model;
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
	

	
	
	
	/**
	 * @return string
	 */
	public function getMyData() {
	    $row = $this->getMapper();
	    unset($row['password']);
	    return $row;
	}
	

	
	
	
	
	/**
	 * getTeams
	 *
	 * @param array $args
	 * @return Teams
	 */
	public function getTotal(array $args = null)
	{
	    $sqlSelect = "SELECT count(*) as num FROM teams ";
	    
	    //die($sqlSelect);
	    $result = $this->query($sqlSelect, []);
	    return $result[0]['num'];
	}
	
	/**
	 * getSports
	 *
	 * @param array $args
	 * @return Teams
	 */
	public function getSports(array $args = null)
	{
	    $sqlSelect = "SELECT * FROM sports ";
	    
	    //die($sqlSelect);
	    $result = $this->query($sqlSelect, []);
	    return $result;
	}
	
	/**
	 * getUsers
	 *
	 * @param array $args
	 * @return Availability
	 */
	public function getRequests(array $args = null)
	{
		$sqlSelect = "SELECT p.*, t.*, o.*, a.*, u.email, u.fbId, CONCAT(u.first_name, ' ', u.last_name) as name, 
		IF(team_id, 'team', IF(player_id, 'player', 'official')) as entity,
		IF(team_id, t.team_name, IF(player_id, CONCAT(pu.first_name, ' ', pu.last_name), CONCAT(ou.first_name, ' ', ou.last_name))) as request_name FROM `requests` a
		JOIN users u ON u.id = a.created_by
		LEFT JOIN players p ON p.id = a.player_id
		LEFT JOIN teams t ON t.id = a.team_id
		LEFT JOIN officials o ON o.id = a.official_id
		LEFT JOIN users pu ON u.id = p.user_id
		LEFT JOIN users ou ON u.id = o.user_id
		WHERE 1 ";
		if(isset($args['id']))
		{
			$sqlSelect .= "AND s.id = :id ";
		}
		if(isset($args['created_by']))
		{
			$sqlSelect .= "AND s.created_by = :created_by ";
		}
		if(isset($args['team_id']))
		{
			$sqlSelect .= "AND a.team_id = :team_id ";
		}
		if(isset($args['player_id']))
		{
			$sqlSelect .= "AND a.player_id = :player_id ";
		}
		if(isset($args['official_id']))
		{
			$sqlSelect .= "AND a.official_id = :official_id ";
		}
		if(isset($args['startFetch']) && isset($args['perPage']))
		{
			$sqlSelect .= "limit ".$args['startFetch'].", ".$args['perPage']." ";
		}
		
		$result = $this->query($sqlSelect, $args);
		
		
		return $result;
	}
	
	
	
	

	
	public function save($data)
	{
		
		//Set default values
		$values['id'] = $data['id'] ?? 0;
		$values['name'] = $data['name'] ?? null;
		$values['gender'] = $data['gender'] ?? null;
		$values['sport'] = $data['sport'] ?? null;
		$values['age_year'] = $data['age_year'] ?? null;
		$values['color'] = $data['color'] ?? null;
		$values['description'] = $data['description'] ?? null;
		$values['user_id'] = $data['user_id'] ?? null;
		$values['created_on'] = $data['created_on'] ?? null;
		
		
		
		
		return $this->insertOrUpdate($values, $data);
	}
	
	public function delete($id)
	{
		$this->tableGateway->delete(['id' => (int) $id]);
	}
}
