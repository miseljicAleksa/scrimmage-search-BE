<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;

class Games extends ModelAbstract implements ModelInterface
{
    
    /**
     *
     * @param \CI_DB_driver $db
     */
    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $dbAdapter->setDbTable(self::getTablePrefix()."games");
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
    public function getGames(array $data = null) {
        $sQuery = "SELECT g.*, t1.team_name as team_one, t2.team_name as team_two, t1.age_group, s.name as sport, t1.team_gender
				FROM ".$this->getDbAdapter()->getDbTable()." g
				JOIN teams t1 on t1.id = g.team_1_id
				JOIN teams t2 on t2.id = g.team_2_id
				JOIN sports s on s.id = g.sport_id
				WHERE 1 ";
        
       
        
        return $this->getDbAdapter()->query($sQuery);
    }
}