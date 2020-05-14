<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;

class Requests extends ModelAbstract implements ModelInterface
{
    
    /**
     *
     * @param \CI_DB_driver $db
     */
    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $dbAdapter->setDbTable(self::getTablePrefix()."requests");
        $this->setDbAdapter($dbAdapter);
    }
    /**
     *
     * @param string $id
     * @throws ApiException
     * @return array
     */
    public function getById(string $id) {
        $sQuery = "SELECT *
				FROM ".$this->getDbAdapter()->getDbTable()."
				WHERE id = '{$id}'
				LIMIT 1";
        return $this->getDbAdapter()->query($sQuery);
    }
    
    /**
     *
     * @param string $email
     * @throws ApiException
     * @return array
     */
    public function getRequests(array $args = null) {
    	
        $sQuery = "SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) as name, 
		IF(team_id, 'team', IF(player_id, 'player', 'official')) as entity,
		IF(team_id, t.team_name, IF(player_id, CONCAT(pu.first_name, ' ', pu.last_name), CONCAT(ou.first_name, ' ', ou.last_name))) as request_name,
		'HI I\'m interested to get in touch with you' as message 
		 from ".$this->getDbAdapter()->getDbTable()." r 
				JOIN users u ON u.id = r.created_by
				LEFT JOIN officials o on o.id = r.official_id
				LEFT JOIN teams t on t.id = r.team_id
				LEFT JOIN players p on p.id = r.player_id
				LEFT JOIN users pu ON pu.id = p.user_id
				LEFT JOIN users ou ON ou.id = o.user_id
				WHERE 1 ";
        
        if(isset($args['created_by']))
        {
        	$sQuery .= 'AND r.created_by = ' . intval($args['created_by']) . ' ';
        	$sQuery .= 'OR o.user_id = ' . intval($args['created_by']) . ' ';
        	$sQuery .= 'OR t.user_id = ' . intval($args['created_by']) . ' ';
        	$sQuery .= 'OR p.user_id = ' . intval($args['created_by']) . ' ';
        }
        $sQuery .= 'GROUP BY r.id';
        
        
        return $this->getDbAdapter()->query($sQuery);
    }
    
}