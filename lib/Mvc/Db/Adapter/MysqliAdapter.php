<?php
/** Comlei Mvc Framework */

namespace Mvc\Db\Adapter;

use Mvc\Db\Statement\Mysqli as MysqliStatement;

/** Mysqli database adapter */
class MysqliAdapter extends AbstractAdapter
{
	/**
	 * A mysqli statement
	 * @var Mvc\Db\Statement\Mysqli
	 */
	protected $stmt;
	
	/**
	 * Setup the database connection
	 * @see \Mvc\Db\Adapter\AdapterInterface::setDb()
	 */
	public function setDb()
	{
		@$this->db = new \mysqli(
			$this->getConfig()->host,
			$this->getConfig()->user,
			$this->getConfig()->pass,
			$this->getConfig()->name
		);
		if ($this->db->connect_error) {
			throw new \Exception($this->db->connect_error);
		} else {
			$this->db->query("SET NAMES 'utf8'");
		}
	}
	
	/**
	 * Prepare a statement and return a PDOStatement-like object.
	 *
	 * @param  string  $sql  SQL query
	 * @return Zend_Db_Statement_Mysqli
	 */
	public function prepare($sql)
	{
		if ($this->getDb()->connect_error) {
			return false;
		}
		if ($this->stmt) {
			$this->stmt->close();
		}
		
		$stmt = new MysqliStatement($this, $sql);
		$stmt->prepare();
		$this->stmt = $stmt;
		return $stmt;
	}
	
	/**
	 * Returns last insert ID
	 * @see \Mvc\Db\Adapter\AdapterInterface::insertedId()
	 */
	public function insertedId()
	{
		return $this->getDb()->insert_id;
	}
	
	/**
	 * Returns number of rows affected by last query
	 * @see \Mvc\Db\Adapter\AdapterInterface::affectedRows()
	 */
	public function affectedRows()
	{
		return $this->getDb()->affected_rows;
	}
}