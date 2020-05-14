<?php
namespace SEI\DbAdapters;

class DbAdapterAbstract {
	
	/**
	 *
	 * @var string
	 */
	protected $dbTable = null;
	
	
	
	/**
	 * Set Db Table
	 *
	 * @param DbAdapterInterface $db
	 */
	public function setDbTable(string $dbTable)
	{
		$this->dbTable = $dbTable;
		return $this;
	}
	
	/**
	 * Get Db Table
	 *
	 */
	public function getDbTable()
	{
		return $this->dbTable;
	}
	
}