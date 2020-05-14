<?php
namespace Workmark\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;

class Player extends ModelAbstract
{
    
    const DIR_UPLOADS = "/data/uploads/";
    const DIR_PLAYER_IMAGE = "player";
	static $table = 'players';
	public $model;
	
	public function getModel() {
	    return $this->model;
	}
	
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
	 * getTotal
	 *
	 * @param array $args
	 * @return Player
	 */
	public function getTotal(array $args = null)
	{
		$sqlSelect = "SELECT count(*) as num FROM players ";
		
		$result = $this->query($sqlSelect, []);
		return $result[0]['num'];
	}
	
	/**
	 * getPlayers
	 *
	 * @param array $args
	 * @return Player
	 */
	public function getPlayers(array $args = null)
	{
	    $sqlSelect = "SELECT p.*, u.email, u.fbId, CONCAT(u.first_name, ' ', u.last_name) as name FROM `players` p
		JOIN users u ON u.id = p.user_id
	        
		WHERE 1 ";
	    if(isset($args['id']))
	    {
	        $sqlSelect .= "AND p.id = :id ";
	    }
	    if(isset($args['interested_in']))
	    {
	        $sqlSelect .= "AND p.interested_in = :interested_in ";
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
		$values['years_of_experience'] = $data['years_of_experience'] ?? null;
		$values['interested_in'] = $data['interested_in'] ?? null;
		$values['image_file_name'] = $data['image_file_name'] ?? null;
		$values['user_id'] = $data['user_id'] ?? null;
		
		
		return $this->insertOrUpdate($values, $data);
	}
	
	public function delete($id)
	{
		$this->tableGateway->delete(['id' => (int) $id]);
	}
	
	static function getImage($type, $id)
	{
	    $file_path = ROOT_PATH . '/data/uploads/' . $type . '/' . $id. '.png';
	    //die(var_dump($file_path));
	    if (file_exists($file_path))
	    {
	        return SITE_URL . '/application/index/show?type=' . $type . '&id=' . $id;
	    }
	    
	    return SITE_URL . '/default.png';
	    
	}
}

