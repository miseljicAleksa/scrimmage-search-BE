<?php
namespace Workmark\Model;

use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;


class Games extends ModelAbstract
{
	static $table = 'games';
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
	 * @return string
	 */
	
	
	/**
	 * @return string
	 */
	public function getTeamName($id) {
	    return $this->getById($id)['name'];
	}
	
	/**
	 * @return string
	 */
	public function getTeamGender($id) {
	    return $this->getById($id)['gender'];
	}
	
	/**
	 * @return string
	 */
	public function getTeamSport($id) {
	    return $this->getById($id)['sport'];
	}
	
	public function getTeamDescription($id) {
	    return $this->getById($id)['description'];
	}
	
	public function getTeamAge($id) {
	    return $this->getById($id)['age_year'];
	}
	
	public function getTeamColor($id) {
	    return $this->getById($id)['color'];
	}
	
	
	public function fetchAll()
	{
		return $this->tableGateway->select();
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
	 * @return Teams
	 */
	public function getScrimages(array $args = null)
	{
		$sqlSelect = "SELECT s.*, u.email, u.fbId, CONCAT(u.first_name, ' ', u.last_name) as name, sp.name as sport_name,
CONCAT(s.address, ', ', s.city, ', ', s.state ) as place, t1.team_name as team_1_name, t2.team_name as team_2_name,  CONCAT(ou.first_name, ' ', ou.last_name) as official_name
	FROM `games` s
		JOIN users u ON u.id = s.user_id
		JOIN sports sp ON sp.id = s.sport_id

		LEFT JOIN teams t1 ON t1.id = s.team_1_id
		LEFT JOIN teams t2 ON t2.id = s.team_2_id
		LEFT JOIN officials o ON o.id = s.official_id
		LEFT JOIN users ou ON u.id = o.user_id
		WHERE 1 ";
		if(isset($args['id']))
		{
			$sqlSelect .= "AND s.id = :id ";
		}
		if(isset($args['user_id']))
		{
			$sqlSelect .= "AND s.user_id = :user_id ";
		}
		if(isset($args['startFetch']) && isset($args['perPage']))
		{
			$sqlSelect .= "limit ".$args['startFetch'].", ".$args['perPage']." ";
		}
		
		$result = $this->query($sqlSelect, $args);
		
		
		return $result;
	}
	
	
	
	
	/**
	 * getUsers
	 *
	 * @param array $args
	 * @return Teams
	 */
	public function getTeamsImage(array $args = null)
	{
	    $sqlSelect = "SELECT u.image_file_name FROM `teams` t
        JOIN users u ON u.id = t.user_id
	        
		WHERE 1 ";
	    if(isset($args['id']))
	    {
	        $sqlSelect .= "AND t.id = :id ";
	    }
	    if(isset($args['user_id']))
	    {
	        $sqlSelect .= "AND t.user_id = :user_id ";
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
