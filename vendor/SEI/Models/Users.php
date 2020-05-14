<?php
namespace SEI\Models;
use SEI\Models\ModelAbstract;
use SEI\ApiException;
use SEI\DbAdapters\DbAdapterInterface;

class Users extends ModelAbstract implements ModelInterface
{
	
	/**
	 * 
	 * @param \CI_DB_driver $db
	 */
	public function __construct(DbAdapterInterface $dbAdapter)
	{
		$dbAdapter->setDbTable(self::getTablePrefix()."users");
		$this->setDbAdapter($dbAdapter);
	}
	/**
	 *
	 * @param string $email
	 * @throws ApiException
	 * @return array
	 */
	public function getById(string $id) {
		$sQuery = "SELECT *
				FROM ".self::getTablePrefix()."users
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
	public function getByEmail(string $email) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new ApiException("Model Users requires valid email parameter");
		}
		$sQuery = "SELECT *
				FROM ".self::getTablePrefix()."users
				WHERE email = '{$email}'
				LIMIT 1";
		return $this->getDbAdapter()->query($sQuery);
	}
	
	/**
	 *
	 * @param string $email
	 * @param string $password
	 * @throws ApiException
	 * @return array
	 */
	public function signup(array $data) {
	    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			throw new ApiException("Model Users requires valid email parameter");
		}
		return $this->getDbAdapter()->insert($data);
	}
}