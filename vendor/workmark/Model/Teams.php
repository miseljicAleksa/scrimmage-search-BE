<?php
namespace Workmark\Model;

use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;


class Teams extends ModelAbstract
{   
    
    const DIR_UPLOADS = "/data/uploads/";
    const DIR_COACH_IMAGE = "coach";
    const DIR_TEAM_IMAGE = "team";
    const DIR_TEAM_LOGO = "logo";
    
	static $table = 'teams';
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
	public function getTeams(array $args = null)
	{
		$sqlSelect = "SELECT t.*, u.image_file_name  FROM `teams` t
		JOIN users u ON u.id = t.user_id
		WHERE 1 ";
		if(isset($args['id']))
		{
			$sqlSelect .= "AND t.id = :id ";
		}
		if(isset($args['user_id']))
		{
			$sqlSelect .= "AND t.created_by = :created_by ";
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
	
	/**
	 * Get team image
	 */
	static function getImage($type, $id)
	{
		$file_path = ROOT_PATH . '/data/uploads/' . $type . '/' . $id. '.png';
		//die(var_dump($file_path));
		if (file_exists($file_path))
		{
			return html_entity_decode(SITE_URL . '/application/index/show?type=' . $type . '&id=' . $id);
		}
		
		return SITE_URL . '/default.png';
		
	}
}
