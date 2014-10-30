<?php
namespace Mvc\Db;

use Mvc\Controller;

class Table
{
	/**
	 * The table name
	 * @var string
	 */
	protected $table;
	
	/**
	 * The Controller
	 * @var Mvc\Controller
	 */
	protected $controller;
	protected $db;
	protected $resultsetClass = 'Mvc\Db\Resultset';
	protected $rowClass = 'Mvc\Db\Row';
	
	public function __construct(Controller $controller, $table = null)
	{
		$this->controller = $controller;
		if($table){
			$this->table = $table;
		}
		if(!$this->table){
			throw new \Exception('Table name not set for table '.get_class($this));
		}
	}
	
	/**
	 * Return controller object
	 * @return \Mvc\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	public function getDb()
	{
		return $this->getController()->getApplication()->getDb();
	}
	
	public function fetch($select, $bind = array()){
		$result = $this->getDb()->query($select, $bind);
		if($result){
			$resultsetClass = $this->resultsetClass;
			$resultset = new $resultsetClass();
			while($row = $result->fetchRow()){
				$rowClass = $this->rowClass;
				$resultset->addRow(new $rowClass($row));
			}
			return $resultset;
		}
		return $result;
	}
	
	public function insert(array $values)
	{
		$keys = array_keys($values);
		array_walk($keys, array($this, 'filterColumnName'));
		$placeholders = $this->getPlaceholders($keys);
		$columns = '`' . implode('`, `', $keys) . '`';
		$query = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
		$result = $this->getDb()->query($query, $values);
		if($result){
			return $this->getDb()->insertedId();
		}
		return null;
	}
	
	public function update($primaryKey, $id, $values)
	{
		$keys = array_keys($values);
		foreach($values as $key => $value){
			$set[] = "`$key` = ?";
		}
		$placeholders = implode(', ', $set);
		$query = "UPDATE $this->table SET $placeholders WHERE $primaryKey = ?";
		$bind = array_values($values);
		$bind[] = $id;
		$result = $this->getDb()->query($query, $bind);
		if($result){
			return $this->getDb()->affectedRows();
		}
		return null;
	}
	
	public function delete($primaryKey, $id)
	{
		$placeholders = $this->getPlaceholders($id);
		$query = "DELETE FROM $this->table WHERE $primaryKey IN( $placeholders )";
		$result = $this->getDb()->query($query, $id);
		if($result){
			return $this->getDb()->affectedRows();
		}
		return null;
	}
	
	public function filterColumnName(&$column)
	{
		return str_replace('`', '', $column);
	}
	
	public function getPlaceholders($columns)
	{
		$placeholders = array_fill(0, count($columns), '?');
		return implode(', ', $placeholders);
	}
	
}