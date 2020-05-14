<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;
use Workmark\Misc;

class Teams extends ModelAbstract implements ModelInterface
{
    
    /**
     *
     * @param \CI_DB_driver $db
     */
    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $dbAdapter->setDbTable(self::getTablePrefix()."teams");
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
    public function getTeams(array $args = null) {
        $sQuery = "SELECT t.*, IFNULL(AVG(r.id), 0) as team_rate, CONCAT(u.first_name, ' ', u.last_name) as coach_name
				FROM ".$this->getDbAdapter()->getDbTable()." t
				JOIN users u on u.id = t.user_id
				LEFT JOIN rates r on t.id = r.team_id 
				WHERE 1 ";
        
        if (isset($args['lat']) && isset($args['lon']) ) {
        	$b = Misc::minMaxDistance($args['lat'], $args['lon'], intval($args['miles_radius']));
        	$sQuery .=  " AND
        	t.lat BETWEEN {$b["S"]} AND {$b["N"]} AND
        	t.lon BETWEEN {$b["W"]} AND {$b["E"]} ";
        }
        
        if(isset($args['home_field_available']) && $args['home_field_available'])
        {
        	$hf = $args['home_field_available'] == "true"?1:0;
        	$sQuery .= 'AND t.has_home_field = ' . $hf . ' ';
        }
        
        if(isset($args['user_id']))
        {
        	$sQuery .= 'AND t.user_id = ' . intval($args['user_id']) . ' ';
        }
        
        if(isset($args['user_id']))
        {
        	$sQuery .= 'AND t.user_id = ' . intval($args['user_id']) . ' ';
        }
        
        $sQuery .= 'GROUP BY t.id ';
        
        if(isset($args['last']))
        {
        	$sQuery .= 'ORDER BY t.id DESC limit 1 ';
        }
        
        
        return $this->getDbAdapter()->query($sQuery);
    }
    
    /**
     *
     * @param string $email
     * @throws ApiException
     * @return array
     */
    public function getAvailableTeams($user_id) {
    	$user_id = intval($user_id);
    	$sQuery = "SELECT t.*
				FROM ".$this->getDbAdapter()->getDbTable()." t
				JOIN requests r on t.id = r.team_id
				WHERE 1 ";
    	
    	$sQuery .= 'AND t.user_id = ' . $user_id. ' 
					OR r.created_by  = ' . $user_id. ' ';
    	
    	
    	return $this->getDbAdapter()->query($sQuery);
    }
    
    
}