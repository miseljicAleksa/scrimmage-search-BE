<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;

class Notifications extends ModelAbstract implements ModelInterface
{
    
    /**
     *
     * @param \CI_DB_driver $db
     */
    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $dbAdapter->setDbTable(self::getTablePrefix()."notfications");
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
    public function getNotifications(array $args = null) {
        $sQuery = "SELECT n.*, r.accepted
				FROM ".$this->getDbAdapter()->getDbTable()." n
				left join requests r on r.id = n.request_id
				WHERE 1 ";
        
        if(isset($args['user_to']))
        {
            $sQuery .= 'AND user_to = ' . intval($args['user_to']) . ' ';
        }
        
        return $this->getDbAdapter()->query($sQuery);
    }
}