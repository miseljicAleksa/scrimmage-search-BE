<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;
use Workmark\Misc;

class Players extends ModelAbstract implements ModelInterface
{
    
    /**
     *
     * @param \CI_DB_driver $db
     */
    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $dbAdapter->setDbTable(self::getTablePrefix()."players");
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
    public function getPlayers($args = null) {
    	$sQuery = "SELECT p.*, IFNULL(AVG(r.id), 0) as player_rate, CONCAT(u.first_name, ' ', u.last_name) as player_name, u.year_born as date_of_birth
				FROM ".$this->getDbAdapter()->getDbTable()." p
				JOIN users u on u.id = p.user_id
				LEFT JOIN rates r on p.id = r.team_id
				WHERE 1 ";
    	
    	if (isset($args['lat']) && isset($args['lon']) ) {
    		$b = Misc::minMaxDistance($args['lat'], $args['lon'], intval($args['miles_radius']));
    		$sQuery .=  " AND
    		p.lat BETWEEN {$b["S"]} AND {$b["N"]} AND
    		p.lon BETWEEN {$b["W"]} AND {$b["E"]} ";
    	}
    	
    	if(isset($args['user_id']))
    	{
    		$sQuery .= 'AND p.user_id = ' . intval($args['user_id']) . ' ';
    	}
    	$sQuery .= 'group by p.id';
    	
    	return $this->getDbAdapter()->query($sQuery);
    }
    
}