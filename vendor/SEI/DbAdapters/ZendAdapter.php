<?php
namespace SEI\DbAdapters;

use SEI\ApiException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;

class ZendAdapter extends DbAdapterAbstract implements DbAdapterInterface
{
	
	/**
	 * DB Driver
	 * 
	 * @var Sql
	 */
	private $db = null;
	
	/**
	 * DB Driver
	 *
	 * @var Sql 
	 */
	private $sql = null;
	
	
	/**
	 *
	 * @param \CI_DB_driver $db
	 */
	public function __construct(DriverInterface $db)
	{
		$this->db = $db;
		$this->sql = new Sql(new Adapter($db));
	}
		
	/**
	* Query
	*
	* @param string $sql
	* @return array
	*/
	public function query(string $sql)
	{
		$adapter = new Adapter($this->db);
		return $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE, new ResultSet())->toArray();
	}
	
	/**
	 * 
	 * @param string $table
	 * @param array $data
	 * @see \SEI\DbAdapterInterface::insert()
	 */
	public function insert(array $data)
	{
		$insert = $this->sql->insert($this->dbTable);
		$insert->values($data);
		$statement = $this->sql->prepareStatementForSqlObject($insert);
		
		$results = $statement->execute();
		if($results)
		{
			return $this->db->getConnection()->getLastGeneratedValue();
		}else{
			return false;
		}
	}
	
	/**
	 *
	 * @param string $table
	 * @param array $data
	 * @see \SEI\DbAdapterInterface::update()
	 */
	public function update(array $data)
	{
		if(!isset($data['id']) || intval($data['id']) < 1)
		{
			throw new ApiException("Update function requires id key in provided data array");
		}
		$update = $this->sql->update($this->dbTable, $data);
		$update->set($data);
		$where = new Where();
		$where->equalTo('id', $data['id']);
		$update->where($where);
		$statement = $this->sql->prepareStatementForSqlObject($update);
		
		return $statement->execute();
	}
}