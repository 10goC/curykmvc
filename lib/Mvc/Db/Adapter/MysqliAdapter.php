<?php
namespace Mvc\Db\Adapter;

use Mvc\Db\Statement\Mysqli as MysqliStatement;

class MysqliAdapter extends AbstractAdapter
{
	protected $stmt;
	
	public function setDb()
	{
		@$this->db = new \mysqli(
			$this->getConfig()->host,
			$this->getConfig()->user,
			$this->getConfig()->pass,
			$this->getConfig()->name
		);
		if($this->db->connect_error){
			throw new \Exception($this->db->connect_error);
		}else{
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
		if($this->getDb()->connect_error){
			return false;
		}
		if($this->stmt){
			$this->stmt->close();
		}
		
		$stmt = new MysqliStatement($this, $sql);
		$stmt->prepare();
		$this->stmt = $stmt;
		return $stmt;
	}
	
	public function insertedId()
	{
		return $this->getDb()->insert_id;
	}
	
	public function affectedRows()
	{
		return $this->getDb()->affected_rows;
	}
}