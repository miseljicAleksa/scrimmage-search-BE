<?php
namespace SEI\Models;

use SEI\DbAdapters\DbAdapterAbstract;
use SEI\DbAdapters\DbAdapterInterface;

/**
 * Model abstract class with some base methods
 * @author arsenleontijevic
 * @since 29.09.2019
 *
 */
abstract class ModelAbstract {


	/**
	 * 
	 * @var DbAdapterInterface
	 */
	private $dbAdapter = null;


/**
 * Set Db Adapter
 * 
 * @param DbAdapterInterface $db
 */
protected function setDbAdapter(DbAdapterInterface $dbAdapter)
{
	$this->dbAdapter = $dbAdapter;
	return $this;
}

/**
 * Get Db Adapter
 * 
 * @param DbAdapterInterface $dbAdapter
 * @return DbAdapterInterface
 */
protected function getDbAdapter()
{
	return $this->dbAdapter;
}

public function insert($data)
{
	return $this->getDbAdapter()->insert($data);
}
	
public function update(array $data) {
	return $this->getDbAdapter()->update($data);
}

/**
 * 
 * @return string
 */
public static function getTablePrefix()
{
	return "";
}
	
}