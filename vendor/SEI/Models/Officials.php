<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;
use Workmark\Misc;

class Officials extends ModelAbstract implements ModelInterface
{
    
    /**
     *
     * @param \CI_DB_driver $db
     */
    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $dbAdapter->setDbTable(self::getTablePrefix()."officials");
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
    public function getOfficials($args = null) {
        $sQuery = "SELECT o.*, IFNULL(AVG(r.id), 0) as official_rate, CONCAT(u.first_name, ' ', u.last_name) as name
				FROM ".$this->getDbAdapter()->getDbTable()." o
				JOIN users u on u.id = o.user_id
				LEFT JOIN rates r on o.id = r.team_id 
				WHERE 1 ";
        
        if (isset($args['lat']) && isset($args['lon']) ) {
        	$b = Misc::minMaxDistance($args['lat'], $args['lon'], intval($args['miles_radius']));
        	$sQuery .=  " AND
        	o.lat BETWEEN {$b["S"]} AND {$b["N"]} AND
        	o.lon BETWEEN {$b["W"]} AND {$b["E"]} ";
        }
        
        if(isset($args['user_id']))
        {
        	$sQuery .= 'AND o.user_id = ' . intval($args['user_id']) . ' ';
        }
        $sQuery .= 'group by o.id';
        
        return $this->getDbAdapter()->query($sQuery);
    }
    
}